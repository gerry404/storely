export function useCategories() {
  const categories = {
    'Mode & Vêtements': [
      'Homme', 'Femme', 'Enfant', 'Chaussures', 'Accessoires', 'Sacs', 'Bijoux',
    ],
    'Beauté & Soins': [
      'Maquillage', 'Soins visage', 'Soins corps', 'Parfums', 'Cheveux', 'Cosmétiques naturels',
    ],
    'Électronique': [
      'Téléphones', 'Accessoires téléphone', 'Ordinateurs', 'Audio', 'TV & Écrans', 'Gadgets',
    ],
    'Maison & Déco': [
      'Meubles', 'Décoration', 'Cuisine', 'Literie', 'Rangement', 'Jardin',
    ],
    'Alimentation': [
      'Épicerie', 'Boissons', 'Pâtisserie', 'Plats préparés', 'Bio & Naturel', 'Condiments',
    ],
    'Art & Artisanat': [
      'Tableaux', 'Sculpture', 'Poterie', 'Tissage', 'Fait main', 'Personnalisé',
    ],
    'Services': [
      'Formation', 'Consultation', 'Design', 'Développement', 'Marketing', 'Autre',
    ],
    'Produits digitaux': [
      'E-books', 'Formations en ligne', 'Templates', 'Musique', 'Photos', 'Logiciels',
    ],
    'Sport & Loisirs': [
      'Équipement', 'Vêtements sport', 'Fitness', 'Jeux', 'Plein air',
    ],
    'Santé & Bien-être': [
      'Compléments', 'Huiles essentielles', 'Yoga & Méditation', 'Équipement médical',
    ],
  }

  const categoryList = Object.keys(categories)

  const getSubcategories = (category) => categories[category] || []

  return {
    categories,
    categoryList,
    getSubcategories,
  }
}
