<?php

namespace App\Controller;

use App\Entity\User;
use App\Security\EmailVerifier;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use Symfony\Component\Mime\Address;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Message\SendConfirmationAccountLinkMessage;
use Symfony\Component\Messenger\MessageBusInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    private EmailVerifier $emailVerifier;
    private UserPasswordHasherInterface $encoder;

    public function __construct(EmailVerifier $emailVerifier, UserPasswordHasherInterface $encoder, private EntityManagerInterface $entityManagerInterface)
    {
        $this->emailVerifier = $emailVerifier;
        $this->encoder = $encoder;
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, MessageBusInterface $messageBus): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {;


            /*
            |--------------------------------------------
            | We check if there already is a user with this username
            |--------------------------------------------
            */
            $userExists = $this->entityManagerInterface->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]);
            if ($userExists) {
                $this->addFlash('danger', 'This username is already taken');
                return $this->redirectToRoute('app_admin');
            }

            $user->setPassword(
                $this->encoder->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $user->setApiToken('apiToken');

            $this->entityManagerInterface->persist($user);
            $this->entityManagerInterface->flush();

            /*
            |--------------------------------------------
            | We use the AccountConfirmationLinkService to send an email to the user to confirm his account
            |--------------------------------------------
            */
            $messageBus->dispatch(new SendConfirmationAccountLinkMessage($user->getId()));
            /*
            |--------------------------------------------
            | We check if the user has been created.
            |--------------------------------------------
            */
            if ($user->getId()) {
                $this->addFlash('success', 'New user just added! Wait... Who are you?');
                return $this->redirectToRoute('app_login');
            } else {
                $this->addFlash('danger', 'Something went wrong...again.. Please, try one more time.');
                return $this->redirectToRoute('app_register');
            }
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/verify', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, EntityManagerInterface $entityManager, VerifyEmailHelperInterface $verifyEmailHelper, UserRepository $userRepository): Response
    {
        $user = $userRepository->findOneBy(['id' => $request->get('id')]);

        if (null === $user) {
            throw $this->createNotFoundException('User not found.');
        }

        try {
            $verifyEmailHelper->validateEmailConfirmation(
                $request->getUri(),
                $user->getId(),
                $user->getEmail()
            );
        } catch (VerifyEmailExceptionInterface $e) {
            $this->addFlash('danger', $e->getReason());

            return $this->redirectToRoute('app_login');
        }


        $user->setIsVerified(true);
        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash('success', 'Your email address has been verified!! Lucky you!');
        return $this->redirectToRoute('app_login', [
            'user' => $user,
        ]);
    }
}
