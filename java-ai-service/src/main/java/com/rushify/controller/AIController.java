package com.rushify.controller;

import com.rushify.service.ProductRecognitionService;
import org.springframework.http.MediaType;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;
import org.springframework.web.multipart.MultipartFile;

import java.util.List;
import java.util.Map;

/**
 * REST Controller – Reconnaissance IA de produits alimentaires
 * Endpoint : POST /api/recognize
 * Consomme : multipart/form-data  (champ "image")
 * Produit   : application/json
 */
@RestController
@RequestMapping("/api")
@CrossOrigin(origins = "*")  // Autoriser les appels depuis PHP (localhost)
public class AIController {

    private final ProductRecognitionService recognitionService;

    public AIController(ProductRecognitionService recognitionService) {
        this.recognitionService = recognitionService;
    }

    /**
     * Analyse une image produit et retourne des suggestions enrichies.
     *
     * Réponse JSON :
     * {
     *   "source": "java-ai",
     *   "suggestions": [
     *     { "name": "Viande fraîche", "category": "Viandes & Charcuterie",
     *       "unit": "kg", "description": "...", "confidence": 0.88 },
     *     ...
     *   ]
     * }
     */
    @PostMapping(value = "/recognize", consumes = MediaType.MULTIPART_FORM_DATA_VALUE)
    public ResponseEntity<Map<String, Object>> recognize(
            @RequestParam("image") MultipartFile image) {

        if (image.isEmpty()) {
            return ResponseEntity.badRequest()
                .body(Map.of("error", "Aucune image fournie."));
        }

        String contentType = image.getContentType();
        if (contentType == null || !contentType.startsWith("image/")) {
            return ResponseEntity.badRequest()
                .body(Map.of("error", "Format de fichier non supporté."));
        }

        try {
            List<Map<String, Object>> suggestions = recognitionService.recognize(image);
            return ResponseEntity.ok(Map.of(
                "source",      "java-ai",
                "suggestions", suggestions
            ));
        } catch (Exception e) {
            return ResponseEntity.internalServerError()
                .body(Map.of("error", "Erreur lors de l'analyse : " + e.getMessage()));
        }
    }

    /** Health check */
    @GetMapping("/health")
    public ResponseEntity<Map<String, String>> health() {
        return ResponseEntity.ok(Map.of("status", "ok", "service", "RUSHIFY AI"));
    }
}
