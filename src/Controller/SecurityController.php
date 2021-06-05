<?php declare(strict_types=1);

/*
The MIT License (MIT)

Copyright (c) 2015-2020 Jacques Archimède

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is furnished
to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/

namespace App\Controller;

use App\Entity\Address;
use App\Entity\User;
use App\Exception\SendMailFailedException;
use App\Form\CreateAccountType;
use App\Security\Authenticator\UserAuthenticator;
use App\Services\NotifyService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

use App\Security\UserManagerInterface;
use App\Security\Util\SecurityFunction;

class SecurityController extends AbstractController
{

	/**
	 * @var int
	 */
	private $retryTtl = 7200;
    /**
     * @var NotifyService
     */
    private $notifyService;

    public function __construct(NotifyService $notifyService)
    {
        $this->notifyService = $notifyService;
    }

	/**
	 * @Route("/login", name="app_login")
	 */
	public function login(AuthenticationUtils $authenticationUtils): Response {
		// get the login error if there is one
		$error = $authenticationUtils->getLastAuthenticationError();
		// last username entered by the user
		$lastUsername = $authenticationUtils->getLastUsername();

		return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
	}

	/**
	 * @Route("/logout", name="app_logout")
	 */
	public function logout() {
		throw new LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
	}

	/**
	 * @Route("/change_password", name="app_change_password")
	 */
	public function changePassword(Request $request, AuthenticationUtils $authenticationUtils, UserPasswordEncoderInterface $passwordEncoder, UserManagerInterface $userManager): Response {
		/** @var User $user */
	    $user = $this->getUser();
		if (!is_object($user) || !$user instanceof UserInterface) {
			throw new AccessDeniedException('This user does not have access to this section.');
		}
		$error = array();
		$success = array();
		// last username entered by the user
		$lastUsername = $authenticationUtils->getLastUsername();

		$form = $request->request->all();
		$password = '';
		$newFirst = '';
		$newSecond = '';
		if (isset($form['_cancel'])) {
			if ($this->isGranted('ROLE_SUPER_ADMIN')) {
				return $this->redirectToRoute('eureka_g6k_admin');
			} else {
				return $this->redirectToRoute('eureka_g6k_index');
			}
		} elseif (isset($form['user_change_password_form'])) {
			$password = $form['user_change_password_form']['current_password'];
			$newFirst = $form['user_change_password_form']['plainPassword']['first'];
			$newSecond = $form['user_change_password_form']['plainPassword']['second'];
			if (!$passwordEncoder->isPasswordValid($user, $password)) {
				$error = [
					'messageKey' => 'security.change_password.error.invalid_current_password',
					'messageData' => []
				];
			} elseif ($newSecond != $newFirst) {
				$error = [
					'messageKey' => 'security.change_password.error.two_passwords_mismatch',
					'messageData' => []
				];
			} elseif (! SecurityFunction::isPasswordStrong($newFirst)) {
				$error = [
					'messageKey' => 'security.change_password.error.password_not_strong',
					'messageData' => []
				];
			} else {
				try {
					$user->setPlainPassword($newFirst);
					$userManager->updateUser($user);
					$success = [
						'messageKey' => 'security.change_password.flash.success',
						'messageData' => []
					];
				} catch(Exception $e) {
					$error = [
						'messageKey' => 'security.change_password.error.change_failed',
						'messageData' => []
					];
				}
				return $this->render('security/change_password_report.html.twig', ['last_username' => $lastUsername, 'success' => $success, 'error' => $error]);
			}
		}
		return $this->render('security/change_password.html.twig', ['last_username' => $lastUsername, 'password' => $password, 'newFirst' => $newFirst, 'newSecond' => $newSecond, 'error' => $error]);
	}

	/**
	 * Request reset user password: show form.
	 *
	 * @Route("/request", name="app_request")
	 */
	public function request(): Response
    {
		$error = array();
		return $this->render('security/request.html.twig', ['error' => $error]);
	}

	/**
	 * @Route("/send-email", name="app_send_email")
	 */
	public function sendEmail(Request $request, UserManagerInterface $userManager): Response {
		if (null !== $request->request->get('_cancel')) {
			if ($this->isGranted('ROLE_SUPER_ADMIN')) {
				return $this->redirectToRoute('eureka_g6k_admin');
			} else {
				return $this->redirectToRoute('eureka_g6k_index');
			}
		}
		$username = $request->request->get('username');
		/** @var User $user */
		$user = $userManager->findUserByUsernameOrEmail($username);

		if (null !== $user) {
			if (!$user->isAccountNonLocked()) {
				return $this->render('security/reset_locked.html.twig', []);
			}
			if (!$user->isPasswordRequestNonExpired($this->retryTtl)) {
				if (null === $user->getConfirmationToken()) {
					$user->setConfirmationToken(SecurityFunction::generateToken());
				}
                try {
                    $result = $this->notifyService->sendMailWithMailJet(
                        2904814,
                        "Driveme.fr : votre demande de réinitialisation de mot de passe",
                        [
                            'userFirstname' => $user->getFirstname(),
                            'reinitialisationUrl' => $this->generateUrl('app_resetting_reset', ['token' => $user->getConfirmationToken()], UrlGeneratorInterface::ABSOLUTE_URL),
                        ],
                        null,
                        $user
                    );
                    if ($result) {
                        $this->addFlash('success', 'Un mail vient de vous être envoyé');
                    } else {
                        $this->addFlash('error', 'Nous n\'avons pas pu vous envoyer de mail, merci de réessayer plus tard');
                    }
                } catch (SendMailFailedException $exception) {
				    // Nothing to do with this error
                }
				$user->setPasswordRequestedAt(new DateTime());
				$userManager->updateUser($user);
				return new RedirectResponse($this->generateUrl('app_check_email', ['username' => $username]));
			}
		}

		return $this->redirectToRoute('app_login');
	}

	/**
	 * @Route("/check-email", name="app_check_email")
	 *
	 * Tell the user to check his email provider.
	 *
	 * @param Request $request
	 *
	 * @return Response
	 */
	public function checkEmail(Request $request): Response
    {
		$username = $request->query->get('username');

		if (empty($username)) {
			// the user does not come from the sendEmail action
			return $this->redirectToRoute('app_request');
		}

		return $this->render('security/check_email.html.twig', [
			'tokenLifetime' => ceil($this->retryTtl / 3600),
		]);
	}

    /**
     * @Route("/reset/{token}", name="app_resetting_reset")
     *
     * Reset user password.
     *
     * @param Request $request
     * @param string $token
     * @param UserManagerInterface $userManager
     * @return Response
     */
	public function reset(Request $request, string $token, UserManagerInterface $userManager): Response
    {
		$user = $userManager->findUserByConfirmationToken($token);

		if (null === $user) {
			return $this->redirectToRoute('app_login');
		}

		$form = $request->request->all();
		$error = array();
		$success = array();

		$newFirst = '';
		$newSecond = '';
		if (isset($form['_cancel'])) {
			if ($this->isGranted('ROLE_SUPER_ADMIN')) {
				return $this->redirectToRoute('eureka_g6k_admin');
			} else {
				return $this->redirectToRoute('eureka_g6k_index');
			}
		} elseif (isset($form['user_resetting_reset_form'])) {
			$newFirst = $form['user_resetting_reset_form']['plainPassword']['first'];
			$newSecond = $form['user_resetting_reset_form']['plainPassword']['second'];
			if ($newSecond != $newFirst) {
				$error = [
					'messageKey' => 'security.change_password.error.two_passwords_mismatch',
					'messageData' => []
				];
			} elseif (! SecurityFunction::isPasswordStrong($newFirst)) {
				$error = [
					'messageKey' => 'security.change_password.error.password_not_strong',
					'messageData' => []
				];
			} else {
				try {
					$user->setPlainPassword($newFirst);
					$user->setConfirmationToken(null);
					$user->setPasswordRequestedAt();
					$userManager->updateUser($user);
					$success = [
						'messageKey' => 'security.change_password.flash.success',
						'messageData' => []
					];
				} catch(Exception $e) {
					$error = [
						'messageKey' => 'security.change_password.error.change_failed',
						'messageData' => []
					];
				}
				return $this->render('security/change_password_report.html.twig', ['last_username' => $user->getUsername(), 'success' => $success, 'error' => $error]);
			}
		}

		return $this->render('security/reset.html.twig', [
			'token' => $token,
			'newFirst' => $newFirst,
			'newSecond' => $newSecond,
			'error' => $error
		]);
	}

    /**
     * Entry point for the creation of an account for a User that isn't connected yet
     *
     * @Route(path="/create-account", name="app_create_account", methods={"get", "post"})
     */
	public function createUserAccount(
        Request $request,
        UserPasswordEncoderInterface $passwordEncoder,
        EntityManagerInterface $entityManager,
        UserAuthenticator $authenticator,
        GuardAuthenticatorHandler $authenticatorHandler
    ): ?Response
    {
        $user = new User();

        if ($request->getSession()->has('shippingAddress')) {
            /** @var Address $shippingAddress */
            $shippingAddress = $request->getSession()->get('shippingAddress');
            if ($shippingAddress->getType() === 0) {
                $user->setFirstname($shippingAddress->getFirstname())
                    ->setLastname($shippingAddress->getLastname())
                ;
            } elseif ($shippingAddress->getType() === 1) {
                $user->setSocialReason($shippingAddress->getSocialReason())
                    ->setSiren($shippingAddress->getSiren())
                ;
            }
        }
        $form = $this->createForm(CreateAccountType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user
                ->setPassword($passwordEncoder->encodePassword($user, $user->getPlainPassword()))
                ->setEmailCanonical($user->getEmail())
                ->setUsernameCanonical($user->getUsername())
                ->setEnabled(true)
            ;

            $user->eraseCredentials();

            $entityManager->persist($user);
            $entityManager->flush();
            try {
                $this->notifyService->sendMailWithMailJet(
                    2905209,
                    "Driveme.fr : votre création de compte",
                    [
                        'userFirstname' => $user->getFirstname(),
                        'profileUrl' => $this->generateUrl('account_index', [], UrlGeneratorInterface::ABSOLUTE_URL)
                    ],
                    null,
                    $user
                );
            } catch (SendMailFailedException $exception) {
                // Nothing to do with this error
            }
            return $authenticatorHandler->authenticateUserAndHandleSuccess($user, $request, $authenticator, 'main');
        }
        return $this->render('user/account/create_account.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route(path="/delete-user", name="app_delete_account", methods={"DELETE"})
     */
    public function deleteAccount(Request $request)
    {
        if (!$this->isGranted('ROLE_USER')) {
            return $this->createAccessDeniedException();
        }
        /** @var User $user */
        $user = $this->getUser();
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $this->container->get('security.token_storage')->setToken(null);
            $em = $this->getDoctrine()->getManager();
            $em->remove($user);
            $em->flush();
            $this->addFlash('success', 'Votre compte utilisateur a bien été supprimé !');
        }
        return $this->redirectToRoute('home_number_plate');
    }
}
