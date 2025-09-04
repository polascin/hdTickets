Create, fix, enhance comprehensive user management system.

User roles:
1. Custormer (customer).
2. Agent (agent).
3. Scraper (scraper) - for system ticket scraping bot functionality purposes only.
3. Administrator (admin).

Administrator has all access and administrative permissions without any limitations.

Prepare and test registration page to register user with customer role for now with e-mail verification and optional 2FA, and with mobile phone number verification. 

Implement system to charge fees.

Due to the nature of the service, it is provided "as is" and the author and operators disclaim any warranty. There is no money-back guarantee.

Create, implement, and put links on welcome page for:
Terms of Service
Disclaimer
Privacy Policy
GDPR Compliance
Data Processing Agreement
Cookie Policy
Acceptable Use Policy
Legal Notices


User have to agree with the agreement to use (Terms of Service), Disclaimer, Data Processing Agreement and Cookie Policy of the HD Tickets web application to obtain valid registration.

When customer e-mail is verified, proceed to verify, if monthly service fee was paid. If so, enable access to limited number of scraped tickets for months. Fee, number of tickets and other optional parameters should be defined in .env file.
If during registration the agent role is chosen, no limit for scaped tickets is applied.
During every login check if the monthly service fee is paid and valid. If not go to the monthly service payment page and verify the payment.

Implement system to purchase scraped tickets.

Take into account information in /docs/administrative-requirements.md

