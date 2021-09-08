<?php

namespace App\Controller;

use App\Entity\Empresa;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

const CLASE_EMPRESA = 'App\Entity\Empresa';

class EmpresasController extends AbstractController
{
    /**
    * @Route("/empresas", name="get_empresas", methods={"GET"})
    */
    public function getEmpresas(): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $repository = $entityManager->getRepository(CLASE_EMPRESA);
        $empresas = $repository->findBy(['activo' => true]);

        return $this->render('empresa/empresa.html.twig', [
            'Empresas' => $empresas
        ]);
    }
}
