<?php

namespace App\Controller;

use App\Entity\Empresa;
use App\Entity\Sector;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Doctrine\ORM\EntityRepository;

const CLASE_EMPRESA = 'App\Entity\Empresa';
const PAGE_SIZE = 10;

class EmpresasController extends AbstractController
{
    /**
    * @Route("/api/v1/empresas", name="get_empresas", methods={"GET"})
    */
    public function getEmpresas(Request $request): Response
    {   
        $empresa = $request->query->get('empresa');
        $sector = $request->query->get('sector');
        $page = $request->query->get('page', 1); // página actual.
        $criteria = ['activo' => true];

        if (!empty($empresa)) {
            // si se indica un nombre de empresa, se filtra por nombre de empresa.
            $criteria += ['nombre' => $empresa];
        }

        if (!empty($sector)) {
            // si se indica un sector, se filtra por sector.
            $criteria += ['sector' => $sector];
        }

        $entityManager = $this->getDoctrine()->getManager();
        $repository = $entityManager->getRepository(CLASE_EMPRESA);
        $empresas = $repository->findBy($criteria, null, PAGE_SIZE, PAGE_SIZE * ($page - 1));
        // Hago un casting a EntityRepository para poder utilizar count()
        // ya que ObjectRepository es su clase padre y count() sólo está definido en el hijo.
        /** @var EntityRepository $repository */
        $empresasCount = $repository->count($criteria); // empresas totales.
        $totalPages = ceil($empresasCount / PAGE_SIZE);

        $data = [ // cuerpo de la respuesta.
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'pageResults' => $this->empresasToArray($empresas),
            'totalCount' => $empresasCount,
            'currentPageSize' => count($empresas),
            'maxPageSize' => PAGE_SIZE
        ];

        return new JsonResponse($data);
    }

    /**
    * @Route("/api/v1/empresas/{id}", name="get_una_empresa", methods={"GET"})
    */
    public function getUnaEmpresa(int $id): JsonResponse
    {
        $entityManager = $this->getDoctrine()->getManager();
       
        $empresa = $entityManager->find(CLASE_EMPRESA, $id);

        return new JsonResponse($this->empresaToArray($empresa));
    }

    /**
    * @Route("/api/v1/empresas", name="add_empresa", methods={"POST"})
    */
    public function addEmpresa(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['nombre']) || empty($data['telefono']) || empty($data['email']) ||
        empty($data['sector'])) {

            throw new NotFoundHttpException('¡Error de validación!');
        }

        $entityManager = $this->getDoctrine()->getManager();
        $sectorRepository = $entityManager->getRepository(Sector::class);
        $sector = $sectorRepository->find($data['sector']);
        if (empty($sector)) {
            throw new NotFoundHttpException('¡Sector no encontrado!');
        }
        $empresa = new Empresa();
        $empresa->setNombre($data['nombre'])
                 ->setTelefono($data['telefono'])
                 ->setEmail($data['email'])
                 ->setSector($sector)
                 ->setActivo(true);

        $entityManager->persist($empresa);
        $entityManager->flush();

        $locationUrl = $this->generateUrl('get_una_empresa', ['id' => $empresa->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse(['status' => '¡Empresa creada!'], Response::HTTP_CREATED, ['Location' => $locationUrl]);
    }

    /**
    * @Route("/api/v1/empresas/{id}", name="update_empresa", methods={"PUT"})
    */
    public function updateEmpresa(int $id, Request $request): JsonResponse
    {
        $entityManager = $this->getDoctrine()->getManager();
        $empresa = $entityManager->find(CLASE_EMPRESA, $id);

        if (!empty($empresa)) {

            $data = json_decode($request->getContent(), true);
            if (!empty($data['sector'])) {
                $sectorRepository = $entityManager->getRepository(Sector::class);
                $sector = $sectorRepository->find($data['sector']);
                if (empty($sector)) {
                    throw new NotFoundHttpException('¡Sector no encontrado!');
                }
                $empresa->setSector($sector);
            }

            empty($data['nombre']) ? true : $empresa->setNombre($data['nombre']);
            empty($data['telefono']) ? true : $empresa->setTelefono($data['telefono']);
            empty($data['email']) ? true : $empresa->setEmail($data['email']);

            $entityManager->flush();
        }

        return new JsonResponse(['status' => '¡Empresa modificada!'], Response::HTTP_NO_CONTENT);
    }

    /**
    * @Route("/api/v1/empresas/{id}/activo", name="update_to_active_empresa", methods={"PUT"})
    */
    public function updateToActiveEmpresa(int $id): JsonResponse
    {
        $entityManager = $this->getDoctrine()->getManager();
        $empresa = $entityManager->find(CLASE_EMPRESA, $id);
        
        if (!empty($empresa)) {

            $empresa->setActivo(true);
            $entityManager->flush();
        }

        return new JsonResponse(['status' => '¡Empresa activada!'], Response::HTTP_NO_CONTENT);
    }

    /**
    * @Route("/api/v1/empresas/{id}", name="delete_empresa", methods={"DELETE"})
    */
    public function deleteEmpresa(int $id): JsonResponse
    {
        $entityManager = $this->getDoctrine()->getManager();
        $empresa = $entityManager->find(CLASE_EMPRESA, $id);
        
        if (!empty($empresa)) {

            $empresa->setActivo(false);
            $entityManager->flush();
        }

        return new JsonResponse(['status' => '¡Empresa borrada!'], Response::HTTP_NO_CONTENT);
    }

    private function empresaToArray($empresa) {
        return [
            'id' => $empresa->getId(),
            'nombre' => $empresa->getNombre(),
            'telefono' => $empresa->getTelefono(),
            'email' => $empresa->getEmail(),
            'sector' => [
                'id' => $empresa->getSector()->getId(),
                'nombre' => $empresa->getSector()->getNombre()
            ]
        ];
    }

    private function empresasToArray($empresas) {
        $array = [];
        foreach ($empresas as $empresa) {
            array_push($array, $this->empresaToArray($empresa));
        }

        return $array;
    }
}
