<?php

namespace App\Controller;

use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends AbstractController
{
    #[Route(path: '/{_locale<%app.supported_locales%>}/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils)
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/{_locale<%app.supported_locales%>}/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }


    #[Route('/{_locale<%app.supported_locales%>}/github/connect', name: 'app_github/connect')]
    public function githubConnectAction(ClientRegistry $clientRegistry)
    {
        // on Symfony 3.3 or lower, $clientRegistry = $this->get('knpu.oauth2.registry');
        // will redirect to Facebook!
        return $clientRegistry
            ->getClient('github') // key used in config/packages/knpu_oauth2_client.yaml
            ->redirect([
                'user' // the scopes you want to access
            ]);
    }

    #[Route('/github/connect/check', name: 'app_github/connect/check')]
    public function githubConnectCheckAction(Request $request, ClientRegistry $clientRegistry)
    {
    }

    #[Route('/{_locale<%app.supported_locales%>}/google/connect', name: 'app_google/connect')]
    public function googleConnectAction(ClientRegistry $clientRegistry)
    {
        // on Symfony 3.3 or lower, $clientRegistry = $this->get('knpu.oauth2.registry');
        // will redirect to Facebook!
        return $clientRegistry
            ->getClient('google') // key used in config/packages/knpu_oauth2_client.yaml
            ->redirect([
                'https://www.googleapis.com/auth/userinfo.profile', 'https://www.googleapis.com/auth/userinfo.email' // the scopes you want to access
            ]);
    }

    #[Route('/google/connect/check', name: 'app_google/connect/check')]
    public function googleConnectCheckAction(Request $request, ClientRegistry $clientRegistry)
    {
    }
}
