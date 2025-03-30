describe('Inscriptions', () => {
    it('test 1 - inscription OK', () => {
        cy.visit('/register');

        cy.get('input[name="registration_form[email]"]').type('test@wr602d.com');
        cy.get('input[name="registration_form[plainPassword][first]"]').type('1234');
        cy.get('input[name="registration_form[plainPassword][second]"]').type('1234');
        cy.get('input[name="registration_form[firstname]"]').type('Max');
        cy.get('input[name="registration_form[lastname]"]').type('Verstappen');

        cy.get('button[type="submit"]').click();
        cy.url().should('include', '/login');
    });

    it('test 2 - inscription KO (password vide)', () => {
        cy.visit('/register');

        cy.get('input[name="registration_form[email]"]').type('test@wr602d.com');
        cy.get('input[name="registration_form[firstname]"]').type('Max');
        cy.get('input[name="registration_form[lastname]"]').type('Verstappen');

        cy.get('button[type="submit"]').click();

        cy.get('input[name="registration_form[plainPassword][first]"]')
            .then(($input) => {
                expect($input[0].checkValidity()).to.be.false;
            });
    });

    it('test 3 - inscription KO (firstname vide)', () => {
        cy.visit('/register');

        cy.get('input[name="registration_form[email]"]').type('test@wr602d.com');
        cy.get('input[name="registration_form[plainPassword][first]"]').type('1234');
        cy.get('input[name="registration_form[plainPassword][second]"]').type('1234');
        cy.get('input[name="registration_form[lastname]"]').type('Verstappen');

        cy.get('button[type="submit"]').click();

        cy.get('input[name="registration_form[firstname]"]')
            .then(($input) => {
                expect($input[0].checkValidity()).to.be.false;
            });
    });

    it('test 4 - inscription KO (lastname vide)', () => {
        cy.visit('/register');

        cy.get('input[name="registration_form[email]"]').type('test@wr602d.com');
        cy.get('input[name="registration_form[plainPassword][first]"]').type('1234');
        cy.get('input[name="registration_form[plainPassword][second]"]').type('1234');
        cy.get('input[name="registration_form[firstname]"]').type('Max');

        cy.get('button[type="submit"]').click();

        cy.get('input[name="registration_form[lastname]"]')
            .then(($input) => {
                expect($input[0].checkValidity()).to.be.false;
            });
    });

    it('test 5 - inscription KO (email invalide sans @)', () => {
        cy.visit('/register');

        cy.get('input[name="registration_form[email]"]').type('testcypress.com');
        cy.get('input[name="registration_form[plainPassword][first]"]').type('1234');
        cy.get('input[name="registration_form[plainPassword][second]"]').type('1234');
        cy.get('input[name="registration_form[firstname]"]').type('Max');
        cy.get('input[name="registration_form[lastname]"]').type('Verstappen');

        cy.get('button[type="submit"]').click();

        cy.get('input[name="registration_form[email]"]')
            .then(($input) => {
                expect($input[0].checkValidity()).to.be.false;
            });
    });

    it('test 6 - inscription KO (email vide)', () => {
        cy.visit('/register');

        cy.get('input[name="registration_form[plainPassword][first]"]').type('1234');
        cy.get('input[name="registration_form[plainPassword][second]"]').type('1234');
        cy.get('input[name="registration_form[firstname]"]').type('Max');
        cy.get('input[name="registration_form[lastname]"]').type('Verstappen');

        cy.get('button[type="submit"]').click();

        cy.get('input[name="registration_form[email]"]')
            .then(($input) => {
                expect($input[0].checkValidity()).to.be.false;
            });
    });

    it('test 7 - inscription KO (confirmation de mot de passe différente)', () => {
        cy.visit('/register');

        cy.get('input[name="registration_form[email]"]').type('test@wr602d.com');
        cy.get('input[name="registration_form[plainPassword][first]"]').type('1234');
        cy.get('input[name="registration_form[plainPassword][second]"]').type('5678');
        cy.get('input[name="registration_form[firstname]"]').type('Max');
        cy.get('input[name="registration_form[lastname]"]').type('Verstappen');

        cy.get('button[type="submit"]').click();

        cy.contains('Les mots de passe ne correspondent pas').should('be.visible');
    });
});