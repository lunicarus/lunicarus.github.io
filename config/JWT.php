<?php

require(__DIR__ . "/vendor/autoload.php");

use \Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTToken
{
    private $key;
    private $server;
    private $timezone;

    public function __construct()
    {
        // Os dados a seguir devem ser alterados para cada site.
        $this->key = "Chave secretíssima aleatória que ninguém deveria saber";
        $this->server = "coloque aqui o nome do servidor ou endereço do site";
        // Ajuste o timezone para usar um timezone onde a maioria dos usuários acessa o site, ou use sem timezone para um sistema internacional.
        $this->timezone = new DateTimeZone('America/Sao_Paulo');
    }

    // Função usada para criar um novo token JWT. O $valid é o tempo de validade do token.


    public function generateToken($userId, $valid = "10")
    {
        $time = new DateTimeImmutable("now", $this->timezone);



        // $time = new DateTimeImmutable();
        $validUntil = $time->modify("+$valid minutes")->getTimestamp();
        $data = [
            "iat" => $time->getTimestamp(),
            "iss" => $this->server,
            "nbf" => $time->getTimestamp(),
            "exp" => $validUntil,
            "sub" => $userId
        ];
        $token = JWT::encode($data, $this->key, 'HS256');
        return $token;
    }

    // Função auxiliar que verifica se o token recebido pelo usuário segue as regras de tempo e validade.
    private function checkJWT($jwt)
    {
        $now = new DateTimeImmutable("now", $this->timezone);
        // $now = new DateTimeImmutable();
        try {
            if ($jwt->iss !== $this->server) {
                throw new Exception("Este token não é para esse servidor");
            }
            if ($jwt->nbf > $now->getTimestamp()) {
                throw new Exception("Este token não pode ser usado antes do tempo mínimo esperado");
            }
            if ($now->getTimestamp() > $jwt->exp) {
                throw new Exception("Este token perdeu a validade");
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    // Retorna o 'userId' do usuário presente no token se possível, ou, qualquer outro caso, false.
    public function getUserId()
    {
        try {
            // Verifica se o campo 'Bearer' com um token foi enviado na requisição
            if (!preg_match('/Bearer\s(\S+)/', $_SERVER['HTTP_AUTHORIZATION'], $matches)) {
                throw new Exception("Token não enviado");
            }
            // Verifica se o campo 'Bearer' é válido.
            $jwt = $matches[1];
            if (!$jwt) {
                throw new Exception("Bearer no formato inválido");
            }
            // Tenta transformar o token recebido em um verdadeiro 'token jwt'. Se falhar, o próprio comando levanta uma exceção.
            $token = JWT::decode($jwt, new Key($this->key, 'HS256'));
            // Verifica se o token em si é válido.
            if(!$this->checkJWT($token)) {
                throw new Exception("Token inválido ou expirado");
            }
            // Tudo certo, retorna então o 'sub' do token.
            return $token->sub;
        } catch (Exception $e) {
            return false;
        }
    }
}
