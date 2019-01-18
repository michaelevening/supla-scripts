<?php

namespace suplascripts\controllers;

use suplascripts\models\Client;
use suplascripts\models\JwtToken;

class ClientsController extends BaseController {
    public function getListAction() {
        $this->ensureAuthenticated();
        $query = $this->getCurrentUser()->clients()->getQuery();
        if ($this->request()->getParam('onlyDevices')) {
            $query = $query->where(Client::SCENE_ID, null);
        }
        $clients = $query->orderBy(Client::LAST_CONNECTION_DATE, 'desc')->get();
        return $this->response($clients);
    }

    public function putAction($params) {
        $this->ensureAuthenticated();
        $client = $this->ensureExists($this->getCurrentUser()->clients()->getQuery()->find($params)->first());
        $parsedBody = $this->request()->getParsedBody();
        $client->update($parsedBody);
        $client->save();
        return $this->response($client);
    }

    public function deleteAction($params) {
        $this->ensureAuthenticated();
        $client = $this->ensureExists($this->getCurrentUser()->clients()->getQuery()->find($params)->first());
        $client->delete();
        return $this->response()->withStatus(204);
    }

    public function createClientForGeneralPurposeAction() {
        $this->ensureAuthenticated();
        return $this->getApp()->db->getConnection()->transaction(function () {
            $client = new Client([Client::LABEL => 'Token #' . ($this->getCurrentUser()->clients()->count() + 1)]);
            $client->purpose = Client::PURPOSE_GENERAL;
            $client->save();
            $token = JwtToken::create()->client($client)->issue();
            $array = $client->toArray();
            $array['token'] = $token;
            return $this->response($array)->withStatus(201);
        });
    }
}
