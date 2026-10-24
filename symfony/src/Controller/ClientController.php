<?php

namespace App\Controller;

use App\Entity\Client;
use App\Repository\ClientRepository;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
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
            return new JsonResponse(['error' => 'Клієнта не знайдено'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(['data' => $client->toArray()], Response::HTTP_OK);
    }

    #[Route('', name: 'create', methods: [Request::METHOD_POST])]
    public function create(Request $request, ClientRepository $repository, EntityManagerInterface $em): JsonResponse
    {
        $body = json_decode($request->getContent(), true) ?? [];

        if (empty($body['firstName']) || empty($body['lastName']) || empty($body['email']) || empty($body['phone']) || empty($body['taxId'])) {
            return new JsonResponse([
                'error' => 'Поля firstName, lastName, email, phone та taxId є обов\'язковими'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Перевірка унікальності email
        if ($repository->findOneBy(['email' => $body['email']])) {
            return new JsonResponse([
                'error' => 'Клієнт із такою електронною поштою вже існує',
                'errors' => ['email' => 'Поле email має бути унікальним']
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Перевірка унікальності taxId (ІПН)
        if ($repository->findOneBy(['taxId' => $body['taxId']])) {
            return new JsonResponse([
                'error' => 'Клієнт із таким податковим номером (taxId) вже існує',
                'errors' => ['taxId' => 'Поле taxId має бути унікальним']
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $client = new Client();
        $client->setFirstName($body['firstName'])
            ->setLastName($body['lastName'])
            ->setEmail($body['email'])
            ->setPhone($body['phone'])
            ->setTaxId($body['taxId']);

        $em->persist($client);
        $em->flush();

        return new JsonResponse([
            'data' => $client->toArray(),
            'message' => 'Клієнта успішно створено'
        ], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: [Request::METHOD_PUT, Request::METHOD_PATCH])]
    public function update(int $id, Request $request, ClientRepository $repository, EntityManagerInterface $em): JsonResponse
    {
        $client = $repository->find($id);
        if (!$client) {
            return new JsonResponse(['error' => 'Клієнта не знайдено'], Response::HTTP_NOT_FOUND);
        }

        $body = json_decode($request->getContent(), true) ?? [];

        // Перевірка унікальності email при зміні
        if (isset($body['email']) && $body['email'] !== $client->getEmail()) {
            if ($repository->findOneBy(['email' => $body['email']])) {
                return new JsonResponse([
                    'error' => 'Клієнт із такою електронною поштою вже існує',
                    'errors' => ['email' => 'Поле email має бути унікальним']
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            $client->setEmail($body['email']);
        }

        // Перевірка унікальності taxId при зміні
        if (isset($body['taxId']) && $body['taxId'] !== $client->getTaxId()) {
            if ($repository->findOneBy(['taxId' => $body['taxId']])) {
                return new JsonResponse([
                    'error' => 'Клієнт із таким податковим номером (taxId) вже існує',
                    'errors' => ['taxId' => 'Поле taxId має бути унікальним']
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            $client->setTaxId($body['taxId']);
        }

        if (isset($body['firstName'])) $client->setFirstName($body['firstName']);
        if (isset($body['lastName'])) $client->setLastName($body['lastName']);
        if (isset($body['phone'])) $client->setPhone($body['phone']);

        $em->flush();

        return new JsonResponse([
            'data' => $client->toArray(),
            'message' => 'Дані клієнта успішно оновлено'
        ], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete', methods: [Request::METHOD_DELETE])]
    public function delete(int $id, ClientRepository $repository, EntityManagerInterface $em): JsonResponse
    {
        $client = $repository->find($id);
        if (!$client) {
            return new JsonResponse(['error' => 'Клієнта не знайдено'], Response::HTTP_NOT_FOUND);
        }

        try {
            $em->remove($client);
            $em->flush();
        } catch (ForeignKeyConstraintViolationException $e) {
            return new JsonResponse([
                'error' => 'Неможливо видалити клієнта, оскільки за ним закріплені активні операції або рахунки'
            ], Response::HTTP_CONFLICT);
        }

        return new JsonResponse(['message' => 'Клієнта успішно видалено'], Response::HTTP_OK);
    }
}
