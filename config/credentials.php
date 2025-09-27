<?php
//VERIFICA SE A CONSTANTE MASTERPASS NAO FOI DEFINIDA
if (!defined('MASTERPASS')) {

    //CREDENCIAIS DE BANCO DE DADOS
    switch (AMBIENTE) {
        case 'DESENV':
            define('DBHOST', 'localhost');
            define('DBNAME', 'mock_desenv_db');
            define('DBUSER', 'mock_user');
            define('DBPASS', 'mock_pass');
            break;

        case 'DESENV2':
            define('DBHOST', 'localhost');
            define('DBNAME', 'mock_desenv2_db');
            define('DBUSER', 'mock_user2');
            define('DBPASS', 'mock_pass2');
            break;

        case 'HOMOLOG':
            define('DBHOST', 'localhost');
            define('DBNAME', 'mock_homolog_db');
            define('DBUSER', 'mock_user_homolog');
            define('DBPASS', 'mock_pass_homolog');
            break;

        case 'PRODUCAO':
            define('DBHOST', 'localhost');
            define('DBNAME', 'mock_producao_db');
            define('DBUSER', 'mock_user_prod');
            define('DBPASS', 'mock_pass_prod');
    }
    //SENHA MESTRE (mock)
    define('MASTERPASS', 'mock_master_pass');
}

/**
 * Função para incluir arquivos de forma relativa
 * @param string $filename
 */
function includeFile($filename)
{
    $arquivo_encontrado = false;
    $iteracoes = 10;
    do {
        if (file_exists($filename)) {
            $arquivo_encontrado = true;
            require_once($filename);
        }
        $iteracoes--;
        $filename = '../' . $filename;
    } while ($arquivo_encontrado == false and $iteracoes > 0);
}
