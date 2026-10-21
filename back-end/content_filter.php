<?php
declare(strict_types=1);

final class SunsetConteudoException extends RuntimeException {}

function sunset_conteudo_normalizar(string $texto): string
{
    $texto = trim($texto);
    $semAcentos = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);
    $texto = $semAcentos !== false ? $semAcentos : $texto;
    $texto = strtolower($texto);
    $texto = strtr($texto, ['4' => 'a', '3' => 'e', '1' => 'i', '0' => 'o', '5' => 's', '7' => 't', '@' => 'a', '$' => 's']);
    return preg_replace('/[^a-z0-9]+/', '', $texto) ?? '';
}

function sunset_conteudo_motivo(string $texto): ?string
{
    $normalizado = sunset_conteudo_normalizar($texto);
    if ($normalizado === '') return null;

    $regras = [
        'exploração sexual de menores' => '/(?:crianca|criancas|menor|menores|adolescente|adolescentes|bebê|bebe).{0,36}(?:sexo|sexual|nude|nudes|porn|explor)/',
        'ameaças, violência ou discurso de ódio' => '/(?:matar|morte|assassinar|assassinato|explodir|explosao|violencia|torturar|tortura|racista|racismo|nazista|odio|amea[cç]a)/',
        'drogas e itens ilícitos' => '/(?:drogas?|cocaina|cocaína|crack|maconha|metanfetamina|fentanil|heroina|heroína|ecstasy|lsd|trafico|tráfico|entorpecente)/',
        'armas e itens ilícitos' => '/(?:arma|armas|pistola|revolver|revólver|fuzil|rifle|municao|munição|explosivo|bomba|granada)/',
        'fraudes, golpes ou pirâmides financeiras' => '/(?:golpe|golpes|fraude|fraudes|piramide|pirâmide|ponzi|renda\s+facil|dinheiro\s+facil|retorno\s+garantido|pix\s+de\s+volta)/',
        'phishing, malware, ransomware ou invasão de sistemas' => '/(?:phishing|malware|ransomware|virus|vírus|hackear|hacker|invas[aã]o|invadir|senha|credencial|keylogger|botnet)/',
        'documentos falsos ou falsificação de identidade' => '/(?:documento\s+falso|documentos\s+falsos|identidade\s+falsa|rg\s+falso|cpf\s+falso|passaporte\s+falso|falsifica[cç][aã]o)/',
        'conteúdo sexual explícito não consentido' => '/(?:nude|nudes|pornografia|pornografico|pornografico|vazar.{0,20}(?:foto|video)|sem\s+consentimento|vingan[cç]a\s+sexual)/',
        'linguagem ofensiva' => '/(?:vai\s+tomar\s+no\s+cu|filho\s+da\s+puta|fdp|puta|puto|vadia|retardado|imbecil|idiota|babaca|ot[aá]rio)/',
    ];
    foreach ($regras as $motivo => $padrao) {
        $textoOriginal = $texto;
        $textoSemSeparadores = preg_replace('/[\s._\-]+/u', '', strtolower($textoOriginal)) ?? $textoOriginal;
        $padraoCompacto = str_replace(['\\s+', '\\s*', '\\s'], '', $padrao);
        if (preg_match($padrao, $textoOriginal) || preg_match($padrao, $textoSemSeparadores) || preg_match($padraoCompacto, $normalizado)) return $motivo;
    }
    return null;
}

function sunset_conteudo_exigir(string $texto, string $campo = 'conteúdo'): string
{
    $motivo = sunset_conteudo_motivo($texto);
    if ($motivo !== null) throw new SunsetConteudoException("O {$campo} foi bloqueado por conter referência a {$motivo}. Remova esse conteúdo e tente novamente.");
    return trim($texto);
}
