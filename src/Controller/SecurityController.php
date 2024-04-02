<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Repository\EntrepriseRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Utilisateur;

#[Route('/api')]
class SecurityController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }


    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
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

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }


    #[Route(path: '/register', name: 'app_register', methods: ['POST'])]
    public function register(Request $request, EntrepriseRepository $entrepriseRepository): Response
    {
        $data = json_decode($request->getContent(), true);

        $entrepriseId = $data['entreprise_id'];

        $entreprise = $entrepriseRepository->find($entrepriseId);

        $utilisateur = new Utilisateur();
        $utilisateur->setPrenom($data['prenom']);
        $utilisateur->setNom($data['nom']);
        $utilisateur->setEmail($data['email']);
        $utilisateur->setDateDeNaissance(new \DateTime($data['date_de_naissance']));
        $utilisateur->setTelephone($data['telephone']);
        $utilisateur->setEntreprise($entreprise);
        $utilisateur->setPassword($data['password']);

        //TODO: FAIRE HASHING PASSWORD

        $entityManager = $this->em;

        $entityManager->persist($utilisateur);
        $entityManager->flush();

        return new Response('Doneee!', Response::HTTP_CREATED);
    }


}
