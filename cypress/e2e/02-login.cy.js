describe('Formulaire de Connexion', () => {
  it('test 1 - connexion OK', () => {
    cy.visit('/login');  // Remplacez par l'URL de votre application

    // Entrer le nom d'utilisateur et le mot de passe
    cy.get('#email').type('test@wr602d.com');
    cy.get('#password').type('1234');

    // Soumettre le formulaire
    cy.get('button[type="submit"]').click();

    // Vérifier que l'utilisateur est connecté
    cy.contains('Transformez vos fichiers en PDF en toute simplicité ! Convertissez du texte, des pages web ou des documents en un seul clic, rapidement et en toute sécurité.').should('exist');
  });

  it('test 2 - connexion KO', () => {
    cy.visit('/login');  // Remplacez par l'URL de votre application

    // Entrer un nom d'utilisateur et un mot de passe incorrects
    cy.get('#email').type('test@cypress.com');
    cy.get('#password').type('5678');

    // Soumettre le formulaire
    cy.get('button[type="submit"]').click();

    // Vérifier que le message d'erreur est affiché
    cy.contains('Adresse-mail et/ou mot de passe incorrect(s)').should('exist');
  });
});

