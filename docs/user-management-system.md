Create, fix, enhance comprehensive user management system.

User roles:
1. Custormer (customer).
2. Agent (agent).
3. Scraper (scraper) - for system ticket scraping bot functionality purposes only.
3. Administrator (admin).

Administrator has all access and administrative permissions without any limitations.

Prepare and test registration page to register user with customer or agent roles with e-mail verification and optional 2FA. Fees are different for customer and for agent and defined in .env file.

Create, implement, and put links on welcome page for:
Terms of Service
Privacy Policy
GDPR Compliance
Data Processing Agreement
Cookie Policy
Acceptable Use Policy
Legal Notices


User have to agree with the agreement to use the HD Tickets web application services. No warranties are guaranteed. Service is offered "as is" without any guarantees and responsibilities. No money back guarantee is offered.

Prepare the agreement and privacy policies to agree with during the registration process and all other administrative requirements.
When customer e-mail is verified, proceed to verify, if monthly service fee was paid. If so, enable access to limited number of scraped tickets for months. Fee, number of tickets and other optional parameters should be defined in .env file.
If during registration the agent role is chosen, no limit for scaped tickets is applied.
During every login check if the monthly service fee is paid and valid. If not go to the monthly service payment page and verify the payment.