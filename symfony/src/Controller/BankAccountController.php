<?php

namespace App\Controller;

use App\Entity\BankAccount;
use App\Repository\BankAccountRepository;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/bank-accounts', name: 'api_bank_accounts_')]
class BankAccountController extends AbstractController
{
    #[Route('', name: 'list', methods: [Request::METHOD_GET])]
    public function list(Request $request, BankAccountRepository $repository): JsonResponse
    {
        $clientId = $request->query->get('clientId');
        if ($clientId) {
            $accounts = $repository->findBy(['client' => $clientId]);
        } else {
            $accounts = $repository->findAll();
        }

        $data = array_map(fn(BankAccount $acc) => $acc->toArray(), $accounts);

        return new JsonResponse(['data' => $data], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'show', methods: [Request::METHOD_GET])]
    public function show(int $id, BankAccountRepository $repository): JsonResponse
    {
        $account = $repository->find($id);
        if (!$account) {
            return new JsonResponse(['error' => 'Bank account not found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(['data' => $account->toArray()], Response::HTTP_OK);
    }

    #[Route('', name: 'create', methods: [Request::METHOD_POST])]
    public function create(Request $request, ClientRepository $clientRepository, EntityManagerInterface $em): JsonResponse
    {
        $body = json_decode($request->getContent(), true) ?? [];

        if (empty($body['clientId']) || empty($body['accountNumber'])) {
            return new JsonResponse(['error' => 'clientId and accountNumber are required'], Response::HTTP_BAD_REQUEST);
        }

        $client = $clientRepository->find($body['clientId']);
        if (!$client) {
            return new JsonResponse(['error' => 'Client not found with id ' . $body['clientId']], Response::HTTP_NOT_FOUND);
        }

        $account = new BankAccount();
        $account->setClient($client)
            ->setAccountNumber($body['accountNumber'])
            ->setBalance((string)($body['balance'] ?? '0.00'))
            ->setCurrency($body['currency'] ?? 'UAH')
            ->setStatus($body['status'] ?? 'active');

        $em->persist($account);
        $em->flush();

        return new JsonResponse(['data' => $account->toArray(), 'message' => 'Bank account created successfully'], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: [Request::METHOD_PUT, Request::METHOD_PATCH])]
    public function update(int $id, Request $request, BankAccountRepository $repository, EntityManagerInterface $em): JsonResponse
    {
        $account = $repository->find($id);
        if (!$account) {
            return new JsonResponse(['error' => 'Bank account not found'], Response::HTTP_NOT_FOUND);
        }

        $body = json_decode($request->getContent(), true) ?? [];

        if (isset($body['balance'])) $account->setBalance((string)$body['balance']);
        if (isset($body['currency'])) $account->setCurrency($body['currency']);
        if (isset($body['status'])) $account->setStatus($body['status']);
        if (isset($body['accountNumber'])) $account->setAccountNumber($body['accountNumber']);

        $em->flush();

        return new JsonResponse(['data' => $account->toArray(), 'message' => 'Bank account updated successfully'], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete', methods: [Request::METHOD_DELETE])]
    public function delete(int $id, BankAccountRepository $repository, EntityManagerInterface $em): JsonResponse
    {
        $account = $repository->find($id);
        if (!$account) {
            return new JsonResponse(['error' => 'Bank account not found'], Response::HTTP_NOT_FOUND);
        }

        $em->remove($account);
        $em->flush();

        return new JsonResponse(['message' => 'Bank account deleted successfully'], Response::HTTP_OK);
    }
}
