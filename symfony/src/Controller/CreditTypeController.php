<?php

namespace App\Controller;

use App\Entity\CreditType;
use App\Repository\CreditTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/credit-types', name: 'api_credit_types_')]
class CreditTypeController extends AbstractController
{
    #[Route('', name: 'list', methods: [Request::METHOD_GET])]
    public function list(CreditTypeRepository $repository): JsonResponse
    {
        $types = $repository->findAll();
        $data = array_map(fn(CreditType $type) => $type->toArray(), $types);

        return new JsonResponse(['data' => $data], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'show', methods: [Request::METHOD_GET])]
    public function show(int $id, CreditTypeRepository $repository): JsonResponse
    {
        $type = $repository->find($id);
        if (!$type) {
            return new JsonResponse(['error' => 'Credit type not found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(['data' => $type->toArray()], Response::HTTP_OK);
    }

    #[Route('', name: 'create', methods: [Request::METHOD_POST])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $body = json_decode($request->getContent(), true) ?? [];

        if (empty($body['name']) || !isset($body['interestRate']) || !isset($body['minAmount']) || !isset($body['maxAmount']) || empty($body['termMonths'])) {
            return new JsonResponse(['error' => 'name, interestRate, minAmount, maxAmount, and termMonths are required'], Response::HTTP_BAD_REQUEST);
        }

        $type = new CreditType();
        $type->setName($body['name'])
            ->setInterestRate((string)$body['interestRate'])
            ->setMinAmount((string)$body['minAmount'])
            ->setMaxAmount((string)$body['maxAmount'])
            ->setTermMonths((int)$body['termMonths']);

        $em->persist($type);
        $em->flush();

        return new JsonResponse(['data' => $type->toArray(), 'message' => 'Credit type created successfully'], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: [Request::METHOD_PUT, Request::METHOD_PATCH])]
    public function update(int $id, Request $request, CreditTypeRepository $repository, EntityManagerInterface $em): JsonResponse
    {
        $type = $repository->find($id);
        if (!$type) {
            return new JsonResponse(['error' => 'Credit type not found'], Response::HTTP_NOT_FOUND);
        }

        $body = json_decode($request->getContent(), true) ?? [];

        if (isset($body['name'])) $type->setName($body['name']);
        if (isset($body['interestRate'])) $type->setInterestRate((string)$body['interestRate']);
        if (isset($body['minAmount'])) $type->setMinAmount((string)$body['minAmount']);
        if (isset($body['maxAmount'])) $type->setMaxAmount((string)$body['maxAmount']);
        if (isset($body['termMonths'])) $type->setTermMonths((int)$body['termMonths']);

        $em->flush();

        return new JsonResponse(['data' => $type->toArray(), 'message' => 'Credit type updated successfully'], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete', methods: [Request::METHOD_DELETE])]
    public function delete(int $id, CreditTypeRepository $repository, EntityManagerInterface $em): JsonResponse
    {
        $type = $repository->find($id);
        if (!$type) {
            return new JsonResponse(['error' => 'Credit type not found'], Response::HTTP_NOT_FOUND);
        }

        $em->remove($type);
        $em->flush();

        return new JsonResponse(['message' => 'Credit type deleted successfully'], Response::HTTP_OK);
    }
}
