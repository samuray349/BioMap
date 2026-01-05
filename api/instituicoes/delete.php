<?php
/**
 * DELETE /instituicoes/:id
 * Delete an institution
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

setCorsHeaders();
handlePreflight();

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    sendError('Method not allowed', 405);
}

try {
    // Get ID from query string or path
    $id = getQueryParam('id');
    if (!$id) {
        preg_match('#/instituicoes/(\d+)#', $_SERVER['REQUEST_URI'], $matches);
        $id = $matches[1] ?? null;
    }
    
    if (!$id || !preg_match('/^\d+$/', $id)) {
        sendError('Invalid ID format. ID must be a number.', 400);
    }
    
    Database::beginTransaction();
    
    try {
        // Check if institution exists and get image URL
        $instituicao = Database::queryOne(
            'SELECT url_imagem FROM instituicao WHERE instituicao_id = ?',
            [$id]
        );
        
        if (!$instituicao) {
            Database::rollback();
            sendError('Instituição not found.', 404);
        }
        
        $imageUrl = $instituicao['url_imagem'];
        
        // Delete the institution
        Database::execute(
            'DELETE FROM instituicao WHERE instituicao_id = ?',
            [$id]
        );
        
        Database::commit();
        
        // After successful DB deletion, try to delete the image file from Hostinger
        if ($imageUrl && trim($imageUrl) !== '' && $imageUrl !== 'img/placeholder.jpg') {
            // Attempt to delete image (non-blocking - errors are logged but don't fail the request)
            deleteImageFile($imageUrl, 'instituicao');
        }
        
        sendJson(['message' => 'Instituição deletada com sucesso.']);
    } catch (Exception $e) {
        Database::rollback();
        throw $e;
    }
} catch (Exception $e) {
    error_log('Erro ao deletar instituição: ' . $e->getMessage());
    sendError('Erro ao deletar instituição.', 500);
}
?>
