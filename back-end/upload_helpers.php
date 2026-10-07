<?php
function salvar_upload_como_jpeg(array $arquivo, string $destino, int $maxBytes, ?array $proporcao = null): bool
{
    if (($arquivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || ($arquivo['size'] ?? 0) <= 0 || $arquivo['size'] > $maxBytes) return false;
    $info = @getimagesize($arquivo['tmp_name']);
    $mime = $info['mime'] ?? '';
    if (!$info || !in_array($mime, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'], true)) return false;
    if ($proporcao && (($info[0] / max(1, $info[1])) < $proporcao[0] || ($info[0] / max(1, $info[1])) > $proporcao[1])) return false;
    if (!function_exists('imagecreatefromstring') || !function_exists('imagejpeg')) return false;
    $imagem = @imagecreatefromstring((string)file_get_contents($arquivo['tmp_name']));
    if (!$imagem) return false;
    $ok = @imagejpeg($imagem, $destino, 90);
    imagedestroy($imagem);
    return $ok && is_file($destino);
}
function salvar_base64_jpeg(string $dataUrl, string $destino, int $maxBytes, ?array $proporcao = null): bool
{
    if (!preg_match('#^data:image/jpeg;base64,#i', $dataUrl)) return false;
    $dados = base64_decode(substr($dataUrl, strpos($dataUrl, ',') + 1), true);
    if ($dados === false || strlen($dados) === 0 || strlen($dados) > $maxBytes) return false;
    $temp = tempnam(sys_get_temp_dir(), 'afde-img-');
    if (!$temp || file_put_contents($temp, $dados, LOCK_EX) === false) { if ($temp) @unlink($temp); return false; }
    $ok = salvar_upload_como_jpeg(['error'=>UPLOAD_ERR_OK, 'size'=>strlen($dados), 'tmp_name'=>$temp], $destino, $maxBytes, $proporcao);
    @unlink($temp);
    return $ok;
}
