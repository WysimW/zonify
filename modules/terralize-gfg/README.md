# Module Terralize GFG - Guide d'utilisation

## 🎯 Objectif
Le module GFG permet de lier les points de vente (POI) à des implantationss et de rediriger automatiquement le bouton "Contacter" vers la page de l'implantations au lieu de la page de contact générale.

## 📋 Étapes d'utilisation

### 1. **Activer le module**

**Nouvelle méthode (recommandée) :**
1. Connectez-vous à votre admin WordPress
2. Allez dans **Terralize > Modules**
3. Trouvez le module **"Module GFG - Gestion des Enseignes"**
4. Activez le toggle vert
5. Cliquez sur **"Enregistrer les paramètres"**

**Ancienne méthode (si la page Modules n'est pas disponible) :**
1. Allez dans **Zones > Module GFG**
2. Cochez **"Activer la gestion des implantationss"**
3. Cliquez **"Enregistrer les paramètres"**

### 2. **Créer des implantationss**

1. Dans le menu admin, cliquez sur **"Enseignes"**
2. Cliquez sur **"Ajouter"**
3. Remplissez :
   - **Titre** : Nom de l'implantations (ex: "Super U", "Carrefour")
   - **Contenu** : Description détaillée de l'implantations
   - **Extrait** : Résumé court
   - **Image mise en avant** : Logo de l'implantations
4. Cliquez **"Publier"**

### 3. **Lier un POI à une implantations**

1. Allez dans **POI > Tous les POI**
2. Cliquez **"Modifier"** sur un POI existant
3. Dans la **sidebar droite**, vous verrez **"Enseigne associée"**
4. Sélectionnez l'implantations dans le menu déroulant
5. Cliquez **"Mettre à jour"**

### 4. **Résultat**

- Sur la carte, cliquez sur un POI lié à une implantations
- Le bouton **"Contacter"** redirige vers `/implantations/nom-implantations/`
- La page de l'implantations affiche tous les POI associés

## 🔧 Fonctionnalités

### ✅ Ce qui fonctionne

- **Relation bidirectionnelle** : POI ↔ Enseigne
- **Redirection automatique** du bouton contact
- **Template responsive** pour les pages implantations
- **Interface d'administration** intuitive
- **Statistiques** dans l'admin
- **Compatibilité** avec le système existant

### 📊 Interface d'administration

Dans **Terralize > Modules**, vous pouvez voir :
- État d'activation du module
- Statistiques : nombre d'implantationss, POI, liaisons
- Lien vers la configuration avancée

## 🛠️ Debugging

### Problèmes courants

**❌ La metabox "Enseigne associée" n'apparaît pas :**
```php
// Vérifiez que le module est activé
get_option('terralize_gfg_enabled'); // Doit retourner true
```

**❌ Les implantationss ne s'affichent pas dans le menu :**
- Vérifiez que le thème a bien le CPT implantations
- Ou que le module crée automatiquement le CPT

**❌ Le bouton contact ne redirige pas :**
- Vérifiez dans la console du navigateur (F12)
- Recherchez le message : `[Popup] POI lié à une implantations`

### Debug console

```javascript
// Sur la page de carte, ouvrez F12 et cherchez :
console.log("[Popup] POI lié à une implantations, redirection vers:", url);
```

## 📝 Structure des fichiers

```
modules/terralize-gfg/
├── terralize-gfg.php          # Module principal
├── single-implantations.php        # Template (copié dans le thème)
└── README.md                  # Ce guide
```

## 🔗 Intégration

Le module s'intègre avec :
- **Shortcode map** : `[tracteur_zone_map]`
- **Popups.js** : Redirection automatique
- **Système de champs** : Compatible avec les champs POI existants
- **Templates** : Template implantations responsive

## 🚀 Évolutions possibles

- Support des logos d'implantations dans les popups
- Filtrage par implantations sur la carte
- Import/export des liaisons POI-Enseigne
- API REST pour les implantationss 