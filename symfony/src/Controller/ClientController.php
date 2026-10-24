<?php

namespace App\Controller;

use App\Entity\Client;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/clients', name: 'api_clients_')]
class ClientController extends AbstractController
{
    #[Route('', name: 'list', methods: [Request::METHOD_GET])]
    public function list(ClientRepository $repository): JsonResponse
    {
        $clients = $repository->findAll();
        $data = array_map(fn(Client $client) => $client->toArray(), $clients);

        return new JsonResponse(['data' => $data], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'show', methods: [Request::METHOD_GET])]
    public function show(int $id, ClientRepository $repository): JsonResponse
    {
        $client = $repository->find($id);
        if (!$client) {
            return new JsonResponse(['error' => 'Client not found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(['data' => $client->toArray()], Response::HTTP_OK);
    }

    #[Route('', name: 'create', methods: [Request::METHOD_POST])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $body = json_decode($request->getContent(), true) ?? [];

        if (empty($body['firstName']) || empty($body['lastName']) || empty($body['email']) || empty($body['phone']) || empty($body['taxId'])) {
            return new JsonResponse(['error' => 'firstName, lastName, email, phone, and taxId are required'], Response::HTTP_BAD_REQUEST);
        }

        $client = new Client();
        $client->setFirstName($body['firstName'])
            ->setLastName($body['lastName'])
            ->setEmail($body['email'])
            ->setPhone($body['phone'])
            ->setTaxId($body['taxId']);

        $em->persist($client);
        $em->flush();

        return new JsonResponse(['data' => $client->toArray(), 'message' => 'Client created successfully'], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: [Request::METHOD_PUT, Request::METHOD_PATCH])]
    public function update(int $id, Request $request, ClientRepository $repository, EntityManagerInterface $em): JsonResponse
    {
        $client = $repository->find($id);
        if (!$client) {
            return new JsonResponse(['error' => 'Client not found'], Response::HTTP_NOT_FOUND);
        }

        $body = json_decode($request->getContent(), true) ?? [];

        if (isset($body['firstName'])) $client->setFirstName($body['firstName']);
        if (isset($body['lastName'])) $client->setLastName($body['lastName']);
        if (isset($body['email'])) $client->setEmail($body['email']);
        if (isset($body['phone'])) $client->setPhone($body['phone']);
        if (isset($body['taxId'])) $client->setTaxId($body['taxId']);

        $em->flush();

        return new JsonResponse(['data' => $client->toArray(), 'message' => 'Client updated successfully'], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete', methods: [Request::METHOD_DELETE])]
    public function delete(int $id, ClientRepository $repository, EntityManagerInterface $em): JsonResponse
    {
        $client = $repository->find($id);
        if (!$client) {
            return new JsonResponse(['error' => 'Client not found'], Response::HTTP_NOT_FOUND);
        }

        $em->remove($client);
        $em->flush();

        return new JsonResponse(['message' => 'Client deleted successfully'], Response::HTTP_OK);
    }
}
