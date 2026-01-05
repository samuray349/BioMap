<?php
/**
 * DELETE /animais/:id
 * Delete an animal
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
        preg_match('#/animais/(\d+)#', $_SERVER['REQUEST_URI'], $matches);
        $id = $matches[1] ?? null;
    }
    
    if (!$id || !preg_match('/^\d+$/', $id)) {
        sendError('Invalid ID format. ID must be a number.', 400);
    }
    
    Database::beginTransaction();
    
    try {
        // Check if animal exists and get image URL
        $animal = Database::queryOne(
            'SELECT url_imagem FROM animal WHERE animal_id = ?',
            [$id]
        );
        
        if (!$animal) {
            Database::rollback();
            sendError('Animal not found.', 404);
        }
        
        $imageUrl = $animal['url_imagem'];
        
        // Delete related records (cascading deletes)
        // 1. Delete avistamentos
        Database::execute(
            'DELETE FROM avistamento WHERE animal_id = ?',
            [$id]
        );
        
        // 2. Delete animal_ameaca relationships
        Database::execute(
            'DELETE FROM animal_ameaca WHERE animal_id = ?',
            [$id]
        );
        
        // 3. Delete the animal
        Database::execute(
            'DELETE FROM animal WHERE animal_id = ?',
            [$id]
        );
        
        Database::commit();
        
        // After successful DB deletion, try to delete the image file from Hostinger
        if ($imageUrl && trim($imageUrl) !== '') {
            // Attempt to delete image (non-blocking - errors are logged but don't fail the request)
            deleteImageFile($imageUrl, 'animal');
        }
        
        sendJson(['message' => 'Animal deletado com sucesso.']);
    } catch (Exception $e) {
        Database::rollback();
        throw $e;
    }
} catch (Exception $e) {
    error_log('Erro ao deletar animal: ' . $e->getMessage());
    sendError('Erro ao deletar animal.', 500);
}
?>
