<?php

namespace App\Controller;

use App\Entity\Sector;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Doctrine\ORM\EntityRepository;

const CLASE_SECTOR = 'App\Entity\Sector';
const PAGE_SIZE = 10;

class SectoresController extends AbstractController
{
    /**
    * @Route("/api/v1/sectores", name="get_sectores", methods={"GET"})
    */
    public function getSectores(Request $request): Response
    {   
        $page = $request->query->get('page', 1); // página actual.
        $criteria = ['activo' => true];

        $entityManager = $this->getDoctrine()->getManager();
        $repository = $entityManager->getRepository(CLASE_SECTOR);
        $sectores = $repository->findBy($criteria, null, PAGE_SIZE, PAGE_SIZE * ($page - 1));
        // Hago un casting a EntityRepository para poder utilizar count()
        // ya que ObjectRepository es su clase padre y count() sólo está definido en el hijo.
        /** @var EntityRepository $repository */
        $sectoresCount = $repository->count($criteria); // empresas totales.
        $totalPages = ceil($sectoresCount / PAGE_SIZE);

        $data = [ // cuerpo de la respuesta.
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'pageResults' => $this->sectoresToArray($sectores),
            'totalCount' => $sectoresCount,
            'currentPageSize' => count($sectores),
            'maxPageSize' => PAGE_SIZE
        ];

        return new JsonResponse($data);
    }

    /**
    * @Route("/api/v1/sectores/{id}", name="get_un_sector", methods={"GET"})
    */
    public function getUnSector(int $id): JsonResponse
    {
        $entityManager = $this->getDoctrine()->getManager();
       
        $sector = $entityManager->find(CLASE_SECTOR, $id);

        return new JsonResponse($this->sectorToArray($sector));
    }

    /**
    * @Route("/api/v1/sectores", name="add_sector", methods={"POST"})
    */
    public function addSector(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['nombre'])) {

            throw new NotFoundHttpException('¡Error de validación!');
        }

        $sector = new Sector();
        $sector->setNombre($data['nombre'])
                ->setActivo(true);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($sector);
        $entityManager->flush();

        $locationUrl = $this->generateUrl('get_un_sector', ['id' => $sector->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse(['status' => '¡Sector creado!'], Response::HTTP_CREATED, ['Location' => $locationUrl]);
    }

    /**
    * @Route("/api/v1/sectores/{id}", name="update_sector", methods={"PUT"})
    */
    public function updateSector(int $id, Request $request): JsonResponse
    {
        $entityManager = $this->getDoctrine()->getManager();
        $sector = $entityManager->find(CLASE_SECTOR, $id);

        if (!empty($sector)) {

            $data = json_decode($request->getContent(), true);
            empty($data['nombre']) ? true : $sector->setNombre($data['nombre']);
        
            $entityManager->flush();
        }

        return new JsonResponse(['status' => '¡Sector modificado!'], Response::HTTP_NO_CONTENT);
    }

    /**
    * @Route("/api/v1/sectores/{id}/activo", name="update_to_active_sector", methods={"PUT"})
    */
    public function updateToActiveSector(int $id): JsonResponse
    {
        $entityManager = $this->getDoctrine()->getManager();
        $sector = $entityManager->find(CLASE_SECTOR, $id);
        
        if (!empty($sector)) {

            $sector->setActivo(true);
            $entityManager->flush();
        }

        return new JsonResponse(['status' => '¡Sector activado!'], Response::HTTP_NO_CONTENT);
    }

    /**
    * @Route("/api/v1/sectores/{id}", name="delete_sector", methods={"DELETE"})
    */
    public function deleteSector(int $id): JsonResponse
    {
        $entityManager = $this->getDoctrine()->getManager();
        $sector = $entityManager->find(CLASE_SECTOR, $id);
        
        if (!empty($sector)) {

            $sector->setActivo(false);
            $entityManager->flush();
        }

        return new JsonResponse(['status' => '¡Sector borrado!'], Response::HTTP_NO_CONTENT);
    }

    private function sectorToArray($sector) {
        return [
            'id' => $sector->getId(),
            'nombre' => $sector->getNombre(),
        ];
    }

    private function sectoresToArray($sectores) {
        $array = [];
        foreach ($sectores as $sector) {
            array_push($array, $this->sectorToArray($sector));
        }

        return $array;
    }
}
