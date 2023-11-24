<?php

    require_once(__DIR__."/../config/Conexao.php");

    class Usuario {


        public static function add($login, $senha, $nome) {
            try {
                // Gera senha criptografada (hasheada, na verdade).
                $senhaCripto = password_hash($senha, PASSWORD_BCRYPT);

                $conexao = Conexao::getConexao();
                $stmt = $conexao->prepare("INSERT INTO USUARIO (login, senha, nome) VALUES (?,?,?)");
                $stmt->execute([$login, $senhaCripto, $nome]);

                return $stmt->rowCount();
            } catch(Exception $e) {
                return null;
            }
        }
        public static function exist($id) {
            try {
                $conexao = Conexao::getConexao();
                $stmt = $conexao->prepare("SELECT COUNT(*) FROM usuario WHERE id = ?");
                $stmt->execute([$id]);

                return $stmt->fetchColumn();
            } catch(Exception $e) {
                return null;
            }
        }
        public static function existLogin($login) {
            try {
                $conexao = Conexao::getConexao();
                $stmt = $conexao->prepare("SELECT COUNT(*) FROM usuario WHERE login LIKE ?");
                $stmt->execute([$login]);

                return $stmt->fetchColumn();
            } catch(Exception $e) {
                return null;
            }
        }
        public static function getLogin($login, $senha) {
            try {
                $conexao = Conexao::getConexao();
                $stmt = $conexao->prepare("SELECT * FROM usuario WHERE login LIKE ?");
                $stmt->execute([$login]);

                $usuario = $stmt->fetchAll()[0];

                // Verifica se o usuário que veio do banco usa a mesma senha digitada pelo usuário fazendo login.

                if(password_verify($senha, $usuario["senha"])) {
                    // É o mesmo usuário! Retorna ele!
                    return $usuario;
                } else {
                    // Não é a mesma senha!
                    return false;
                }
            } catch(Exception $e) {
                return null;
            }
        }
    }