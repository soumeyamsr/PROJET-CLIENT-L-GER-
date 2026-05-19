package com.rushify.service;

import org.springframework.stereotype.Service;
import org.springframework.web.multipart.MultipartFile;

import javax.imageio.ImageIO;
import java.awt.*;
import java.awt.image.BufferedImage;
import java.io.InputStream;
import java.util.*;
import java.util.List;

/**
 * Service de reconnaissance de produits alimentaires par analyse d'image.
 *
 * Architecture :
 *   1. Extraire la couleur dominante de l'image (réduction à 8×8 pixels)
 *   2. Calculer un score de teinte HSB dominant
 *   3. Mapper ce signal couleur sur une taxonomie alimentaire
 *   4. Retourner une liste de suggestions triées par confiance
 *
 * En production : remplacer analyzeImage() par un appel à
 * Google Cloud Vision API, AWS Rekognition, ou un modèle DL4J fine-tuné
 * sur un dataset food-101.
 */
@Service
public class ProductRecognitionService {

    // Taxonomie produits alimentaires RUSHIFY
    private static final List<ProductEntry> PRODUCT_DB = List.of(
        new ProductEntry("red",     "Viande fraîche",              "Viandes & Charcuterie",     "kg",     "Viande fraîche de qualité professionnelle.",        0.88),
        new ProductEntry("pink",    "Charcuterie",                  "Viandes & Charcuterie",     "kg",     "Charcuterie artisanale.",                           0.82),
        new ProductEntry("salmon",  "Saumon frais",                 "Poissons & Fruits de mer",  "kg",     "Saumon atlantique, pêche durable.",                 0.85),
        new ProductEntry("grey",    "Poisson frais",                "Poissons & Fruits de mer",  "kg",     "Poisson frais du jour.",                            0.78),
        new ProductEntry("green",   "Légumes verts frais",          "Légumes & Fruits",          "kg",     "Légumes de saison, origine locale.",                0.87),
        new ProductEntry("orange",  "Carottes / Agrumes",           "Légumes & Fruits",          "kg",     "Produits orangés frais.",                           0.80),
        new ProductEntry("yellow",  "Fromage / Produit laitier",    "Produits laitiers",         "kg",     "Fromage ou produit laitier frais.",                 0.83),
        new ProductEntry("white",   "Produit laitier blanc",        "Produits laitiers",         "L",      "Lait, crème ou yaourt frais.",                      0.75),
        new ProductEntry("beige",   "Boulangerie / Pâtisserie",     "Boulangerie & Pâtisserie",  "pièce",  "Produit de boulangerie artisanal fait maison.",     0.84),
        new ProductEntry("brown",   "Produit épicerie",             "Épicerie sèche",             "kg",     "Produit d'épicerie fine.",                          0.72),
        new ProductEntry("purple",  "Fruits rouges / Betterave",    "Légumes & Fruits",          "kg",     "Fruits rouges ou légumes violets frais.",           0.76),
        new ProductEntry("neutral", "Produit alimentaire",          "Autres",                    "kg",     "Produit alimentaire professionnel.",                0.60)
    );

    public List<Map<String, Object>> recognize(MultipartFile file) throws Exception {
        String colorLabel = analyzeImage(file);
        return buildSuggestions(colorLabel);
    }

    // ── Analyse couleur dominante ──────────────────────────────────
    private String analyzeImage(MultipartFile file) throws Exception {
        try (InputStream in = file.getInputStream()) {
            BufferedImage img = ImageIO.read(in);
            if (img == null) return "neutral";

            // Réduction à 8x8 pour extraire la couleur dominante rapidement
            BufferedImage small = new BufferedImage(8, 8, BufferedImage.TYPE_INT_RGB);
            Graphics2D g2 = small.createGraphics();
            g2.setRenderingHint(RenderingHints.KEY_INTERPOLATION, RenderingHints.VALUE_INTERPOLATION_BILINEAR);
            g2.drawImage(img, 0, 0, 8, 8, null);
            g2.dispose();

            long totalR = 0, totalG = 0, totalB = 0;
            for (int x = 0; x < 8; x++) {
                for (int y = 0; y < 8; y++) {
                    Color c = new Color(small.getRGB(x, y));
                    totalR += c.getRed();
                    totalG += c.getGreen();
                    totalB += c.getBlue();
                }
            }
            int pixels = 64;
            int r = (int)(totalR / pixels);
            int g = (int)(totalG / pixels);
            int b = (int)(totalB / pixels);

            // Conversion HSB pour catégorisation
            float[] hsb = Color.RGBtoHSB(r, g, b, null);
            float hue        = hsb[0] * 360f;
            float saturation = hsb[1];
            float brightness = hsb[2];

            // Gris / blanc / beige → faible saturation
            if (saturation < 0.15f) {
                if (brightness > 0.85f) return "white";
                if (brightness > 0.50f) return "beige";
                return "brown";
            }

            // Catégorisation par teinte
            if (hue < 15 || hue >= 345)                         return r > 160 ? "red" : "pink";
            if (hue >= 15  && hue < 45)                         return "orange";
            if (hue >= 45  && hue < 75)                         return "yellow";
            if (hue >= 75  && hue < 150)                        return "green";
            if (hue >= 150 && hue < 200 && brightness < 0.6f)   return "grey";
            if (hue >= 150 && hue < 200)                        return "salmon";
            if (hue >= 200 && hue < 270)                        return "grey";
            if (hue >= 270 && hue < 330)                        return "purple";
            return "neutral";
        }
    }

    // ── Construction des suggestions ─────────────────────────────
    private List<Map<String, Object>> buildSuggestions(String colorLabel) {
        // Priorité : correspondance exacte, puis fallback "neutral"
        List<Map<String, Object>> results = new ArrayList<>();

        PRODUCT_DB.stream()
            .filter(e -> e.colorKey.equals(colorLabel))
            .limit(2)
            .forEach(e -> results.add(e.toMap()));

        // Toujours ajouter une option générique si < 3 résultats
        if (results.size() < 3) {
            PRODUCT_DB.stream()
                .filter(e -> e.colorKey.equals("neutral"))
                .findFirst()
                .ifPresent(e -> results.add(e.toMap(0.55)));
        }
        return results;
    }

    // ── Modèle interne ────────────────────────────────────────────
    private record ProductEntry(
        String colorKey,
        String name,
        String category,
        String unit,
        String description,
        double confidence
    ) {
        Map<String, Object> toMap() {
            return toMap(confidence);
        }
        Map<String, Object> toMap(double conf) {
            Map<String, Object> m = new LinkedHashMap<>();
            m.put("name",        name);
            m.put("category",    category);
            m.put("unit",        unit);
            m.put("description", description);
            m.put("confidence",  conf);
            return m;
        }
    }
}
