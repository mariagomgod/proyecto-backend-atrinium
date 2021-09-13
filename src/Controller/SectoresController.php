<?php

namespace App\Controller;

use App\Entity\Sector;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Doctrine\ORM\EntityRepository;

const CLASE_SECTOR = 'App\Entity\Sector';

class SectoresController extends AbstractController
{
    const PAGE_SIZE = 10;

    /**
    * @Route("/api/v1/sectores", name="get_sectores", methods={"GET"})
    */
    public function getSectores(Request $request): Response
    {   
        $page = $request->query->get('page', 1); // página actual.
        $getAll = $request->query->get('all', false); // devolver todos?

        $entityManager = $this->getDoctrine()->getManager();
        $repository = $entityManager->getRepository(CLASE_SECTOR);
        $pageSize = $getAll ? null : self::PAGE_SIZE;
        $offset = $getAll ? null : self::PAGE_SIZE * ($page - 1);
        $sectores = $repository->findBy([], ['id' => 'ASC'], $pageSize, $offset);
        // Hago un casting a EntityRepository para poder utilizar count()
        // ya que ObjectRepository es su clase padre y count() sólo está definido en el hijo.
        /** @var EntityRepository $repository */
        $sectoresCount = $repository->count([]); // sectores totales.
        $totalPages = ceil($sectoresCount / self::PAGE_SIZE);

        $data = [ // cuerpo de la respuesta.
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'pageResults' => $this->sectoresToArray($sectores),
            'totalCount' => $sectoresCount,
            'currentPageSize' => count($sectores),
            'maxPageSize' => self::PAGE_SIZE
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
        $nombre = $data['nombre'];
        if (!isset($nombre)) {
            throw new BadRequestException('¡Error de validación!');
        }

        $entityManager = $this->getDoctrine()->getManager();
        $repository = $entityManager->getRepository(CLASE_SECTOR);
        $existingSector = $repository->findBy(['nombre' => $nombre]);
        if (!empty($existingSector)) {
            throw new ConflictHttpException('¡Sector ya existe!');
        }

        $sector = new Sector();
        $sector->setNombre($nombre);
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
            isset($data['nombre']) ? $sector->setNombre($data['nombre']) : true;
        
            $entityManager->flush();
        }

        return new JsonResponse(['status' => '¡Sector modificado!'], Response::HTTP_NO_CONTENT);
    }

    /**
    * @Route("/api/v1/sectores/{id}", name="delete_sector", methods={"DELETE"})
    */
    public function deleteSector(int $id): JsonResponse
    {
        $entityManager = $this->getDoctrine()->getManager();
        $sector = $entityManager->find(CLASE_SECTOR, $id);
        
        if (!empty($sector)) {

            $entityManager->remove($sector);
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
