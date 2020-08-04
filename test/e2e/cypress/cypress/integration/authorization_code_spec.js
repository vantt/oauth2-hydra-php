describe('Authorization Code Test', () => {
    it('It should be redirected to the login page', () => {

        cy.visit({url: '/test-connect/authorization-code'} )
        cy.url().should('contains', '/login?login_challenge');

        cy.get('#email').type('foo@bar.com');
        cy.get('#password').type('foobar');
        cy.get('#accept').click();
        cy.url().should('contains', '/consent?consent_challenge');

        cy.get('#offline').click();
        cy.get('#offline_access').click();
        cy.get('#accept').click();
        cy.url().should('contains', '/test-connect/authorization-code');

        cy.get('pre').then(($pre) => {
            const json = JSON.parse($pre.text());

            expect(json).to.have.property('accessToken');
            expect(json.accessToken).to.have.property('scope', 'offline offline_access') // true
            expect(json.accessToken).to.have.property('token_type', 'bearer') // true

            expect(json.accessToken).to.have.property('access_token') // true
            expect(json.accessToken.access_token).to.be.a('string')

            expect(json.accessToken).to.have.property('expires') // true
            //expect(json.accessToken.expires).to.be.a('string')

            expect(json).to.have.property('resourceOwner');
        })
    });
})
