<?php

class Mensagem {

    private static function construir($css, $texto) {
        $_icone = array(
            'success' => 'bi-check-lg',
            'danger' => 'bi-exclamation-triangle',
            'info' => 'bi-info-circle',
            'warning' => 'bi-patch-minus'
        );
        $_procurado = array('á', 'é', 'í', 'ó', 'ú', 'ç', 'ã', 'à', 'â', 'ê', 'ô', 'Á', 'É', 'Í', 'Ó', 'Ú', 'Ç', 'Ã', 'À', 'Â', 'Ê', 'Ô');
        $_substituto = array('&aacute;', '&eacute;', '&iacute;', '&oacute;', '&uacute', '&ccedil;', '&atilde;', '&agrave;', '&acirc;', '&ecirc;', '&ocirc;', '&Aacute;', '&Eacute;', '&Iacute;', '&Oacute;', '&Uacute', '&Ccedil;', '&Atilde;', '&Agrave;', '&Acirc;', '&Ecirc;', '&Ocirc;');

        $html = '<div class="alert alert-' . $css . ' alert-dismissible fade show mb-0 pr-3" role="alert">';
        $html .= '<span class="bi ' . $_icone[$css] . ' mr-2 f-s-18"></span>';
        $html .= str_replace($_procurado, $_substituto, $texto);
        //$html .= '<button type="button" class="close float-right" data-dismiss="alert" aria-label="Close" style="font-size:20px">';
        //$html .= '<span aria-hidden="true">&times;</span>';
        $html .= '</button>';
        $html .= '</div>';
        return $html;
    }
    public static function erro($msg, $sessao = FALSE) {
        $_msg = array(
            'texto' => $msg,
            'tipo' => 'danger',
            'html' => self::construir('danger', $msg)
        );
        if($sessao){
            Sessao::adicionar('msg', $_msg);
        }
        else{
            return $_msg;
        }
    }
    public static function sucesso($msg, $sessao = FALSE) {
        $_msg = array(
            'texto' => $msg,
            'tipo' => 'success',
            'html' => self::construir('success', $msg)
        );
        if($sessao){
            Sessao::adicionar('msg', $_msg);
        }
        else{
            return $_msg;
        }
    }
    public static function atencao($msg, $sessao = FALSE) {
        $_msg = array(
            'texto' => $msg,
            'tipo' => 'warning',
            'html' => self::construir('warning', $msg)
        );
        if($sessao){
            Sessao::adicionar('msg', $_msg);
        }
        else{
            return $_msg;
        }
    }    
    public static function informacao($msg, $sessao = FALSE) {
        $_msg = array(
            'texto' => $msg,
            'tipo' => 'info',
            'html' => self::construir('info', $msg)
        );
        if($sessao){
            $_SESSION['msg'] = $_msg;
            Sessao::adicionar('msg', $_msg);
        }
        else{
            return $_msg;
        }
    }
    public static function buscar(){
        if(Sessao::existe('msg')){
            $_msg = Sessao::getVariavel('msg');
            Sessao::remover('msg');
            return $_msg['html'];
        }
    }
}