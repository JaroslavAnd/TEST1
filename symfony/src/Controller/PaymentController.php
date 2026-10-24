<?php

namespace App\Controller;

use App\Entity\Payment;
use App\Repository\CreditRepository;
use App\Repository\PaymentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/payments', name: 'api_payments_')]
class PaymentController extends AbstractController
{
    #[Route('', name: 'list', methods: [Request::METHOD_GET])]
    public function list(Request $request, PaymentRepository $repository): JsonResponse
    {
        $creditId = $request->query->get('creditId');
        if ($creditId) {
            $payments = $repository->findBy(['credit' => $creditId]);
        } else {
            $payments = $repository->findAll();
        }

        $data = array_map(fn(Payment $payment) => $payment->toArray(), $payments);

        return new JsonResponse(['data' => $data], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'show', methods: [Request::METHOD_GET])]
    public function show(int $id, PaymentRepository $repository): JsonResponse
    {
        $payment = $repository->find($id);
        if (!$payment) {
            return new JsonResponse(['error' => 'Payment not found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(['data' => $payment->toArray()], Response::HTTP_OK);
    }

    #[Route('', name: 'create', methods: [Request::METHOD_POST])]
    public function create(Request $request, CreditRepository $creditRepo, EntityManagerInterface $em): JsonResponse
    {
        $body = json_decode($request->getContent(), true) ?? [];

        if (empty($body['creditId']) || empty($body['amount'])) {
            return new JsonResponse(['error' => 'creditId and amount are required'], Response::HTTP_BAD_REQUEST);
        }

        $credit = $creditRepo->find($body['creditId']);
        if (!$credit) {
            return new JsonResponse(['error' => 'Credit not found'], Response::HTTP_NOT_FOUND);
        }

        $paymentAmount = (string)$body['amount'];

        $payment = new Payment();
        $payment->setCredit($credit)
            ->setAmount($paymentAmount)
            ->setPaymentMethod($body['paymentMethod'] ?? 'bank_transfer')
            ->setStatus($body['status'] ?? 'completed')
            ->setPaymentDate(!empty($body['paymentDate']) ? new \DateTime($body['paymentDate']) : new \DateTime());

        // Update remaining amount on credit
        $newRemaining = max(0, (float)$credit->getRemainingAmount() - (float)$paymentAmount);
        $credit->setRemainingAmount((string)$newRemaining);
        if ($newRemaining == 0) {
            $credit->setStatus('paid');
        }

        $em->persist($payment);
        $em->flush();

        return new JsonResponse(['data' => $payment->toArray(), 'message' => 'Payment recorded successfully'], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: [Request::METHOD_PUT, Request::METHOD_PATCH])]
    public function update(int $id, Request $request, PaymentRepository $repository, EntityManagerInterface $em): JsonResponse
    {
        $payment = $repository->find($id);
        if (!$payment) {
            return new JsonResponse(['error' => 'Payment not found'], Response::HTTP_NOT_FOUND);
        }

        $body = json_decode($request->getContent(), true) ?? [];

        if (isset($body['amount'])) $payment->setAmount((string)$body['amount']);
        if (isset($body['paymentMethod'])) $payment->setPaymentMethod($body['paymentMethod']);
        if (isset($body['status'])) $payment->setStatus($body['status']);

        $em->flush();

        return new JsonResponse(['data' => $payment->toArray(), 'message' => 'Payment updated successfully'], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete', methods: [Request::METHOD_DELETE])]
    public function delete(int $id, PaymentRepository $repository, EntityManagerInterface $em): JsonResponse
    {
        $payment = $repository->find($id);
        if (!$payment) {
            return new JsonResponse(['error' => 'Payment not found'], Response::HTTP_NOT_FOUND);
        }

        $em->remove($payment);
        $em->flush();

        return new JsonResponse(['message' => 'Payment deleted successfully'], Response::HTTP_OK);
    }
}
