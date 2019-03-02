<?php

namespace Phambinh\LaraSocketPusher\WebSocket;

use Exception;
use React\EventLoop\LoopInterface;
use React\EventLoop\TimerInterface;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use Validator;

class Pusher implements MessageComponentInterface
{
    protected $clients;

    protected $userToClients;

    public function __construct()
    {
        $this->clients = [];
        $this->userToClients = [];
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients[$conn->resourceId] = $conn;

        return $conn->send(json_encode([
            'type' => 'RESPONSE_YOUR_CLIENT_ID',
            'data' => [
                'client_id' => $conn->resourceId
            ]
        ]));
    }

    public function onClose(ConnectionInterface $conn)
    {
        if (isset($this->clients[$conn->resourceId])) {
            unset($this->clients[$conn->resourceId]);
        }
        if (isset($conn->userId) && isset($this->userToClients[$conn->userId][$conn->resourceId])) {
            unset($this->userToClients[$conn->userId][$conn->resourceId]);
        }
    }

    /**
     * @param $entry array đã được json encode
     */
    public function onTrigger($entry)
    {
        echo $entry . "\n";

        $entry = json_decode($entry);

        $validator = $this->validateTrigger($entry);

        if ($validator->fails()) {
            return $conn->send(json_encode([
                'type' => 'ERROR',
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ]));
        }

        $data = $entry->data;
        $type = $entry->type;
        $usersId = isset($entry->users_id) ? $entry->users_id : [];

        if (!$usersId) {
            return;
        }

        $users = [];

        if ($usersId[0] == '*') {
            $users = $this->userToClients;
        } else {
            foreach ($usersId as $userId) {
                if (isset($this->userToClients[$userId])) {
                    $users[$userId] = $this->userToClients[$userId];
                }
            }
        }

        foreach ($users as $clients) {
            foreach ($clients as $client) {
                $payload = [
                    'type' => $type,
                    'data' => $data
                ];
                $client->send(json_encode($payload));
            }
        }
    }

    public function onMessage(ConnectionInterface $conn, $data)
    {
        $data = json_decode($data);

        if (!$data) {
            return $conn->send(json_encode([
                'type' => 'ERROR',
                'message' => 'Không thể xử lý dữ liệu bạn gửi lên. Vui lòng gửi dữ liệu dạng json!'
            ]));
        }

        if (!isset($data->type)) {
            return $conn->send(json_encode([
                'type' => 'ERROR',
                'message' => 'Trường type là bắt buộc'
            ]));
        }

        switch ($data->type) {
            case 'SEND_MY_USER_ID':
                $validator = $this->validateConnectUserToClient($data);

                if ($validator->fails()) {
                    return $conn->send(json_encode([
                        'type' => 'ERROR',
                        'message' => $validator->errors()->first(),
                        'errors' => $validator->errors()
                    ]));
                }

                $client = $this->getClient($data->client_id);

                $this->userAttachClient($data->user_id, $client);

                return $conn->send(json_encode([
                    'type' => 'CONNECT_YOUR_USER_ID_TO_CLIENT_ID_SUCCESS',
                    'message' => 'Connect success',
                ]));
                break;
        }
    }

    public function onError(ConnectionInterface $conn, Exception $e)
    {
        try {
            $conn->close();
            if (isset($this->conns[$conn->resourceId])) {
                unset($this->conns[$conn->resourceId]);
            }
            if (isset($conn->userId) && isset($this->userToClients[$conn->userId][$conn->resourceId])) {
                unset($this->userToClients[$conn->userId][$conn->resourceId]);
            }
            throw $e;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getClientsOfUser($userId)
    {
        try {
            return $this->userToClients[$userId];
        } catch (Exception $e) {
            return null;
        }
    }

    public function getClient($resourceId)
    {
        return $this->clients[$resourceId];
    }

    public function userAttachClient($userId, $client)
    {
        $client->userId = $userId;
        $this->clients[$client->resourceId] = $client;
        $this->userToClients[$userId][$client->resourceId] = $client;
    }

    public function validateTrigger($data)
    {
        return Validator::make((array) $data, [
            'data' => 'nullable|required',
            'users_id' => 'array'
        ], [
            'data.required' => 'Thiếu trường data',
            'users_id.array' => 'Users ID phải ở dạng mảng'
        ]);
    }

    public function validateConnectUserToClient($data)
    {
        return Validator::make((array)$data, [
            'client_id' => 'required',
            'user_id' => 'required',
            'type' => 'required'
        ], [
            'type.required' => 'Type không được để trống',
            'client_id.required' => 'Client ID không được để trống',
            'user_id.required' => 'User ID không được để trống'
        ]);
    }
}
