describe('Formulaire de Connexion', () => {
  it('test 1 - connexion OK', () => {
    cy.visit('http://localhost:8319/login');  // Remplacez par l'URL de votre application

    // Entrer le nom d'utilisateur et le mot de passe
    cy.get('#email').type('buteauquentin10@gmail.com');
    cy.get('#password').type('quentin');

    // Soumettre le formulaire
    cy.get('button[type="submit"]').click();

    // Vérifier que l'utilisateur est connecté
    cy.contains('You are logged in as Quentin BUTEAU').should('exist');
  });

  it('test 2 - connexion KO', () => {
    cy.visit('http://localhost:8319/login');  // Remplacez par l'URL de votre application

    // Entrer un nom d'utilisateur et un mot de passe incorrects
    cy.get('#username').type('buteauquentin10@gmail.com');
    cy.get('#password').type('qnb');

    // Soumettre le formulaire
    cy.get('button[type="submit"]').click();

    // Vérifier que le message d'erreur est affiché
    cy.contains('Invalid credentials.').should('exist');
  });
});
