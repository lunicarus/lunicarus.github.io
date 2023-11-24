<?php
require_once("config/JWT.php");
require_once("config/utils.php");
require_once("config/verbs.php");
require_once("config/header.php");
require_once("model/Usuario.php");


// Tentar pegar o 'id' do usuário logado, ou false, se não tiver logado.
$jwt = new JWTToken();
$usuario = $jwt->getUserId();

if (isMetodo("GET")) {
    try {
        // Usuário quer fazer operação de logout.
        if (parametrosValidos($_GET, ["logout"])) {
            // Usuário não pode fazer 'logout' se nem logado está!
            if (!$usuario) {
                throw new Exception("Usuário não está logado", 401);
            }
            /* Aqui você deve usar uma estratégia para invalidar o token, como por exemplo armazenar o token em uma lista-negra (blacklist) no banco de dados, fazer a troca da chave secreta (fazer um esquema de chave secreta única para cada usuário, por exemplo), entre outras abordagens. Nesse exemplo, nada disso foi feito. Apenas dizemos "ok, o token foi removido". Mas na prática nada mudou. Saiba disso antes de fazer um site 'real'. */
            output(200, ["msg" => "Deslogado com sucesso"]);
        }

        // Usuário quer acessar a página e sem querer dar logout. Ele está logado? Se não está, retorna erro.
        if (!$usuario) {
            throw new Exception("Usuário não está logado", 401);
        }
        // Ok, usuário está logado. Mas será que o "id" do usuário ainda está válido/existe?
        if (!Usuario::exist($usuario)) {
            // Se não existe, remove o token inválida e avisa o usuário do problema. Ver comentário da invalidação do token acima.            
            throw new Exception("Usuário não existe mais. Você foi automaticamente deslogado", 400);
        }
        // Logado e id existe. Faz o que deve fazer (coloquei só um simples "Olá").
        output(200, ["msg" => "Olá! Parece que tá tudo OK!"]);
    } catch (Exception $e) {
        output($e->getCode(), ["msg" => $e->getMessage()]);
    }
}


if (isMetodo("POST")) {
    try {
        // Usuário está logado? Se estiver, ele não poderia fazer POST. Cancela a requisição
        if($usuario) {
            throw new Exception("Você está logado e não pode fazer isso", 400);
        }
        
        // Usuário não está logado e quer fazer o cadastro.
        if (parametrosValidos($_POST, ["signup", "login", "nome", "senha"])) {
            // Verifica se o login já não existe no sistema.
            if(Usuario::existLogin($_POST["login"])) {
                throw new Exception("Este login já existe", 400);
            }
            $res = Usuario::add($_POST["login"], $_POST["senha"], $_POST["nome"]);
            if(!$res) {
                throw new Exception("Erro no cadastro", 500);
            }
            output(200, ["msg" => "Cadastro realizado com sucesso"]);
        }

        // Usuário não está logado e quer fazer o login.
        if (parametrosValidos($_POST, ["login", "senha"])) {
            // Verifica se o login existe no sistema. Se não existe, rejeita o usuário.
            if(!Usuario::existLogin($_POST["login"])) {
                throw new Exception("Este login não existe", 400);
            }

            $res = Usuario::getLogin($_POST["login"], $_POST["senha"]);
            if(!$res) {
                throw new Exception("Login ou senha inválida", 400);
            }
            // Tudo pronto, hora de criar um token JWT que será enviado no corpo da resposta ao usuário.
            $token = $jwt->generateToken($res["id"]);
            output(200, ["msg" => "Login realizado com sucesso", "token" => $token]);
        }

        throw new Exception("Requisição não reconhecida", 400);
    } catch (Exception $e) {
        output($e->getCode(), ["msg" => $e->getMessage()]);
    }
}
