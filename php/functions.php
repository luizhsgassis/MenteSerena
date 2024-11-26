<?php
// Função para validar a data
function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

// Função para validar CPF
function validateCPF($cpf) {
    return strlen($cpf) == 11 && ctype_digit($cpf);
}

// Função para validar telefone
function validateTelefone($telefone) {
    return strlen($telefone) == 11 && ctype_digit($telefone);
}