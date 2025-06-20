<?php

namespace App\Security;

use App\Entity\Panier;
use App\Repository\UsersRepository;
use App\Service\PanierHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class AppAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';
    

    public function __construct(private UrlGeneratorInterface $urlGenerator, private UsersRepository $usersRepo, private EntityManagerInterface $em, private PanierHandler $panierHandler)
    {
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->getPayload()->getString('email');

        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $email);

        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($request->getPayload()->getString('password')),
            [
                new CsrfTokenBadge('authenticate', $request->getPayload()->getString('_csrf_token')),
                new RememberMeBadge(),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        // à la connexion le champ de dernière connexion est mis à jour
        $user = $this->usersRepo->findOneBy(['email' => $request->get('email')]);
        $user->setLastLogIn(new \DateTimeImmutable('now'));

        // à la connexion les paniers et leur état sont vérifiés
        // si besoin, un nouveau panier est créé pour être le panier actif de l'utilisateur
        $paniers = $user->getPaniers();
        if (!empty($paniers) && !$user->getPanierActif()) {
            foreach ($paniers as $panier) {
                if ($panier->getEtat() === 1) {
                    $user->setPanierActif($panier);
                    break;
                }
            }
            
            if ($user->getPanierActif() === null) {
                $newPanier = new Panier($user);
                $this->em->persist($newPanier);
    
                $user->addPanier($newPanier);
                $user->setPanierActif($newPanier);
            }
        } elseif(empty($paniers)) {
            $panier = new Panier($user);
            $this->em->persist($panier);
    
            $user->addPanier($panier);
            $user->setPanierActif($panier);
        }

        $this->em->persist($user);
        $this->em->flush();

        // redirection vers la page du site une fois l'authetication terminée
        return new RedirectResponse($this->urlGenerator->generate('app_index'));
        throw new \Exception('TODO: provide a valid redirect inside '.__FILE__);
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
