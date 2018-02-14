<?php

namespace suplascripts\controllers;

use Assert\InvalidArgumentException;
use Slim\Http\Request;
use Slim\Http\Response;
use suplascripts\controllers\exceptions\ApiException;
use suplascripts\controllers\exceptions\Http403Exception;
use suplascripts\controllers\exceptions\Http404Exception;
use suplascripts\models\HasApp;
use suplascripts\models\supla\SuplaApiException;
use suplascripts\models\User;
use Throwable;

abstract class BaseController {

    use HasApp;

    protected function response($content = null): Response {
        return $this->getApp()->response->withJson($content);
    }

    protected function request(): Request {
        return $this->getApp()->request;
    }

    /**
     * @return User
     */
    protected function getCurrentUser() {
        return $this->getApp()->getCurrentUser();
    }

    protected function ensureAuthenticated() {
        if (!$this->getCurrentUser()) {
            throw new Http403Exception();
        }
    }

    protected function ensureExists($object, $errorMessage = 'Element not found') {
        if (!$object) {
            throw new Http404Exception($errorMessage);
        }
        return $object;
    }

    protected function beforeAction() {
    }

    public function __call($methodName, $args) {
        if (count($args) == 3) { // request, response, args
            $action = $methodName . 'Action';
            $startTime = microtime(true);
            $endpoint = 'endpoint.' . array_slice(explode('\\', get_class($this)), -1)[0] . ".$methodName.";
            try {
                $this->beforeAction();
                $response = call_user_func_array([&$this, $action], [$args[2]]);
                $this->getApp()->metrics->increment($endpoint . 'success');
            } catch (Throwable $e) {
                $response = $this->exceptionToResponse($e);
                $this->getApp()->metrics->increment($endpoint . 'failure');
            }
            $elapsedTime = round((microtime(true) - $startTime) * 1000);
            $this->getApp()->metrics->timing($endpoint . 'time', $elapsedTime);
            return $response;
        }
        throw new \BadMethodCallException("There is no method $methodName.");
    }

    private function exceptionToResponse(Throwable $e) {
        if ($e instanceof SuplaApiException) {
            $this->getApp()->logger->toSuplaLog()->warning($e->getMessage());
            $this->getApp()->metrics->increment('error.supla');
            return $this->response(['message' => $e->getMessage(), 'data' => $e->getData()])->withStatus($e->getCode());
        } elseif ($e instanceof ApiException) {
            $this->getApp()->logger->warning('Action execution failed.', ['message' => $e->getMessage()]);
            $this->getApp()->metrics->increment('error.api');
            return $this->response(['message' => $e->getMessage(), 'data' => $e->getData()])->withStatus($e->getCode());
        } elseif ($e instanceof InvalidArgumentException) {
            $this->getApp()->logger->info('Validation failed.', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            $this->getApp()->metrics->increment('error.validation');
            return $this->response([
                'message' => $e->getMessage(),
                'reason' => $e->getPropertyPath(),
            ])->withStatus(422);
        } else {
            error_log($e);
            $this->getApp()->logger->error($e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $this->getApp()->metrics->increment('error.exception');
            return $this->response([
                'status' => 500,
                'message' => $e->getMessage(),
            ])->withStatus(500);
        }
    }
}
