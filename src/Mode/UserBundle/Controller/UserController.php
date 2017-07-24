<?php

namespace Mode\UserBundle\Controller;
use FOS\RestBundle\View\View;
use Mode\UserBundle\Entity\Etat;
use Mode\UserBundle\Entity\Role;
use Mode\UserBundle\Entity\User;
use Mode\UserBundle\Form\Type\EtatType;
use Mode\UserBundle\Form\Type\RoleType;
use Mode\UserBundle\Form\UserType;
use Mode\UserBundle\Entity\Reponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest; // alias pour toutes les annotations



class UserController extends Controller{

    /**
     * @Rest\View(serializerGroups={"admin"})
     * @Rest\Get("/rest/secure/client/admin/users")
     */
    public function getUsersAction(Request $request)
    {
        $users = $this->get('doctrine.orm.entity_manager')
            ->getRepository('ModeUserBundle:User')
            ->findAll();


        return $users;
    }

    /**
     * @Rest\View(serializerGroups={"user"})
     * @Rest\Get("/rest/secure/client/publicateur/users/{id}")
     */
    public function getUserAction(Request $request)
    {
        $user = $this->getDoctrine()
            ->getManager()
            ->getRepository('ModeUserBundle:User')
            ->find($request->get('id'));

        if (empty($user)) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('User not found');
        }


        return $user;
    }
    /**
     * @Rest\View(statusCode=Response::HTTP_CREATED, serializerGroups={"user"})
     * @Rest\Post("/rest/secure/client/users")
     */
    public function postUsersAction(Request $request)
    {

        $data = $request->request->all();
        $userManager = $this->get('fos_user.user_manager');
        $user = $userManager->createUser();
        $form = $this->createForm(UserType::class, $user);

        $form->submit($data); // Validation des données

        if ($form->isValid()) {
            $user->setEnabled(true);
            $user->addRole("ROLE_PUBLICATEUR");
            $userManager->updateUser($user);
            $reponse=array("code"=>201,"message"=>"User créé avec succès");
            return $reponse;
        } else {
            return $form;
        }

    }
    /**
     * @Rest\View(statusCode=Response::HTTP_NO_CONTENT, serializerGroups={"user"})
     * @Rest\Delete("/rest/secure/client/admin/users/{id}")
     */
    public function removeUserAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $user = $em->getRepository('ModeUserBundle:User')
            ->find($request->get('id'));

        if (!$user) {
            return;
        }

        foreach ($user->getCommunes() as $commune) {
            $em->remove($commune);
        }

        $em->remove($user);
        $em->flush();
    }
    /**
     * @Rest\View(serializerGroups={"user"})
     * @Rest\Put("/rest/secure/client/publicateur/users/{id}")
     */
    public function updateUserAction(Request $request)
    {
        $user = $this->get('doctrine.orm.entity_manager')
            ->getRepository('ModeUserBundle:User')
            ->find($request->get('id'));
        if (empty($user)) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('User not found');
        }
        $form = $this->createForm(UserType::class, $user);
        $form->submit($request->request->all(),false);
        if ($form->isValid()) {
            $userManager = $this->get('fos_user.user_manager');
            $userManager->updateUser($user);
            $reponse= array("code"=>200,"message"=>"User modifié avec succès");
            return $reponse;
        } else {
            return $form;
        }

    }
    /**
     * @Rest\View(serializerGroups={"admin"})
     * @Rest\Put("/rest/secure/client/admin/users/{id}/etat")
     */
    public function updateEtatUserAction(Request $request)
    {
        $user = $this->get('doctrine.orm.entity_manager')
            ->getRepository('ModeUserBundle:User')
            ->find($request->get('id'));
        if (empty($user)) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('User not found');
        }
        $etat = new Etat();
        $form = $this->createForm(EtatType::class, $etat);
        $form->submit($request->request->all());
        if ($form->isValid()) {
            if ($etat->getEnabled()==1)
                $user->setEnabled(true);
            else
                $user->setEnabled(false);
            $userManager = $this->get('fos_user.user_manager');
            $userManager->updateUser($user);
            return $user;
        } else {
            return $form;
        }

    }
    /**
     * @Rest\View(serializerGroups={"admin"})
     * @Rest\Post("/rest/secure/client/admin/users/{id}/roles")
     */
    public function addRolesAction(Request $request)
    {
        $role = new Role();
        $form = $this->createForm(RoleType::class, $role);

        $form->submit($request->request->all());

        if (!$form->isValid()) {
            return $form;
        }
        $em = $this->get('doctrine.orm.entity_manager');
        $user = $em->getRepository('ModeUserBundle:User')->find($request->get('id'));

        if (empty($user)) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('User not found');
        }

        $user->addRole($role->getNom());

        $userManager = $this->get('fos_user.user_manager');
        $userManager->updateUser($user);
        $reponse= array("code"=>200,"message"=>"Rôle ajouté avec succès");
        return $reponse;
    }
    /**
     * @Rest\View(serializerGroups={"admin"})
     * @Rest\Delete("/rest/secure/client/admin/users/{id}/roles")
     */
    public function removeRolesAction(Request $request)
    {
        $role = new Role();
        $form = $this->createForm(RoleType::class, $role);

        $form->submit($request->request->all());

        if (!$form->isValid()) {
            return $form;
        }
        $em = $this->get('doctrine.orm.entity_manager');
        $user = $em->getRepository('ModeUserBundle:User')->find($request->get('id'));

        if (empty($user)) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('User not found');
        }

        $user->removeRole($role->getNom());
        $userManager = $this->get('fos_user.user_manager');
        $userManager->updateUser($user);
        $reponse= array("code"=>200,"message"=>"Rôle supprimé avec succès");
        return $reponse;

    }

}
