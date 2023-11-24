<?php

/*
    Classe PHP que contémm um singleton que gera a conexão para um banco de dados usando PDO.
    Atenção que os valores de hostname, database, username e password dependendem do ambiente sendo utilizado.
    Lembre-se de colocar esse arquivo preferencialmente, em algum local com acesso restrito (.htaccess?)
*/

class Conexao
{
    private static $instancia;

    private function __construct()
    {
        if (in_array($_SERVER['REMOTE_ADDR'], ["127.0.0.1", "::1"])) {
            // O servidor roda na própria máquina
            $hostname = 'localhost:3306';
            $database = 'locadora_terca';
            $username = 'root';
            $password = '';
        } else {
            $hostname = 'XXXX';
            $database = 'XXXX';
            $username = 'XXXX';
            $password = 'XXXX';
        }

        $dsn = "mysql:host=$hostname;dbname=$database";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ];

        try {
            self::$instancia = new PDO($dsn, $username, $password, $options);
        } catch (Exception $e) {
            echo $e->getMessage();
            exit;
        }
    }

    public static function getConexao()
    {
        if (!isset(self::$instancia)) {
            new Conexao();
        }
        return self::$instancia;
    }
}
