<?php

/**
 * Nyalife HMS - Home Controller
 *
 * Controller for home page and public services.
 */

require_once __DIR__ . '/WebController.php';

class HomeController extends WebController
{
    // Override to allow public access without login
    /** @var bool */
    protected $requiresLogin = false;

    /**
     * Initialize the controller
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Show home page
     */
    public function index(): void
    {
        $this->pageTitle = 'Home - Nyalife HMS';

        // Get services data
        $services = $this->getServices();

        $this->renderView('home/index', [
            'services' => $services,
            'isLoggedIn' => Auth::getInstance()->isLoggedIn(),
            // Mark as landing so global dashboard padding is NOT applied
            'isLanding' => true
        ]);
    }

    /**
     * Show services page
     */
    public function services(): void
    {
        $this->pageTitle = 'Our Services - Nyalife HMS';

        $services = $this->getServices();

        $this->renderView('home/services', [
            'services' => $services
        ]);
    }

    /**
     * Show obstetrics services
     */
    public function obstetricsServices(): void
    {
        $this->pageTitle = 'Obstetrics Care - Nyalife HMS';

        $this->renderView('home/services/obstetrics', [
            'service' => $this->getObstetricsService()
        ]);
    }

    /**
     * Show gynecology services
     */
    public function gynecologyServices(): void
    {
        $this->pageTitle = 'Gynecology Services - Nyalife HMS';

        $this->renderView('home/services/gynecology', [
            'service' => $this->getGynecologyService()
        ]);
    }

    /**
     * Show laboratory services
     */
    public function laboratoryServices(): void
    {
        $this->pageTitle = 'Laboratory Services - Nyalife HMS';

        $this->renderView('home/services/laboratory', [
            'service' => $this->getLaboratoryService()
        ]);
    }

    /**
     * Show pharmacy services
     */
    public function pharmacyServices(): void
    {
        $this->pageTitle = 'Pharmacy Services - Nyalife HMS';

        $this->renderView('home/services/pharmacy', [
            'service' => $this->getPharmacyService()
        ]);
    }

    /**
     * Show about page - redirects to home page with about anchor
     */
    public function about(): void
    {
        // Redirect to home page with about anchor
        $this->redirect('/#about');
    }

    /**
     * Show contact page - redirects to home page with contact anchor
     */
    public function contact(): void
    {
        // Redirect to home page with contact anchor
        $this->redirect('/#contact');
    }

    /**
     * Process contact form
     */
    public function sendContact(): void
    {
        try {
            // Validate required fields
            $requiredFields = ['name', 'email', 'subject', 'message'];

            foreach ($requiredFields as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception("Missing required field: $field");
                }
            }

            // Validate email
            if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Invalid email address');
            }

            // Process contact form (implement email sending)
            $this->sendContactEmail($_POST);

            SessionManager::set('auth_message', 'Thank you for your message. We will get back to you soon.');
            SessionManager::set('auth_message_type', 'success');
            $this->redirect('/#contact');
        } catch (Exception $e) {
            SessionManager::set('auth_message', $e->getMessage());
            SessionManager::set('auth_message_type', 'error');
            $this->redirect('/#contact');
        }
    }

    /**
     * Subscribe to newsletter
     */
    public function subscribeNewsletter(): void
    {
        try {
            if (empty($_POST['email'])) {
                throw new Exception('Email address is required');
            }

            if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Invalid email address');
            }

            // Store newsletter subscription (implement database storage)
            $this->storeNewsletterSubscription($_POST['email']);

            SessionManager::set('auth_message', 'Thank you for subscribing to our newsletter!');
            SessionManager::set('auth_message_type', 'success');
            $this->redirectToRoute('home');
        } catch (Exception $e) {
            SessionManager::set('auth_message', $e->getMessage());
            SessionManager::set('auth_message_type', 'error');
            $this->redirectToRoute('home');
        }
    }

    /**
     * Redirect to Facebook
     */
    public function redirectToFacebook(): void
    {
        header('Location: https://www.facebook.com/nyalifewomensclinic');
        exit;
    }

    /**
     * Redirect to Twitter
     */
    public function redirectToTwitter(): void
    {
        header('Location: https://twitter.com/nyalifeclinic');
        exit;
    }

    /**
     * Redirect to Instagram
     */
    public function redirectToInstagram(): void
    {
        header('Location: https://www.instagram.com/nyalifewomensclinic');
        exit;
    }

    /**
     * Redirect to LinkedIn
     */
    public function redirectToLinkedIn(): void
    {
        header('Location: https://www.linkedin.com/company/nyalife-womens-clinic');
        exit;
    }

    /**
     * Get all services
     *
     * @return array Services data
     */
    private function getServices(): array
    {
        return [
            'obstetrics' => $this->getObstetricsService(),
            'gynecology' => $this->getGynecologyService(),
            'laboratory' => $this->getLaboratoryService(),
            'pharmacy' => $this->getPharmacyService()
        ];
    }

    /**
     * Get obstetrics service data
     *
     * @return array Service data
     */
    private function getObstetricsService(): array
    {
        return [
            'title' => 'Obstetrics Care',
            'icon' => 'fas fa-baby',
            'description' => 'Comprehensive prenatal, delivery, and postnatal care for expectant mothers.',
            'features' => [
                'Prenatal care and monitoring',
                'Ultrasound screenings',
                'Genetic testing',
                'High-risk pregnancy care',
                'Labor and delivery services',
                'Postnatal care'
            ]
        ];
    }

    /**
     * Get gynecology service data
     *
     * @return array Service data
     */
    private function getGynecologyService(): array
    {
        return [
            'title' => 'Gynecology Services',
            'icon' => 'fas fa-venus',
            'description' => 'Expert care for women\'s reproductive health and wellness.',
            'features' => [
                'Routine examinations',
                'Pap smears and HPV testing',
                'Family planning',
                'Endometriosis treatment',
                'PCOS management',
                'Menopause care'
            ]
        ];
    }

    /**
     * Get laboratory service data
     *
     * @return array Service data
     */
    private function getLaboratoryService(): array
    {
        return [
            'title' => 'Laboratory Services',
            'icon' => 'fas fa-microscope',
            'description' => 'State-of-the-art diagnostic and testing facilities.',
            'features' => [
                'Blood tests and analysis',
                'Urinalysis',
                'Hormone level testing',
                'Genetic testing',
                'Amniotic fluid analysis',
                'Various health screenings'
            ]
        ];
    }

    /**
     * Get pharmacy service data
     *
     * @return array Service data
     */
    private function getPharmacyService(): array
    {
        return [
            'title' => 'Pharmacy Services',
            'icon' => 'fas fa-pills',
            'description' => 'Full-service pharmacy with prescription and over-the-counter medications.',
            'features' => [
                'Prescription medications',
                'Over-the-counter drugs',
                'Medication counseling',
                'Prenatal vitamins',
                'Hormonal treatments',
                'Drug interaction checks'
            ]
        ];
    }

    /**
     * Send contact email
     *
     * @param array $data Contact form data
     */
    private function sendContactEmail(array $data): void
    {
        // Implementation for sending contact email
        // This would integrate with your email system
        error_log("Contact form submitted: " . json_encode($data));

        // TODO: Implement actual email sending
        // You can use PHPMailer, SwiftMailer, or your preferred email library
    }

    /**
     * Store newsletter subscription
     *
     * @param string $email Email address
     */
    private function storeNewsletterSubscription($email): void
    {
        // Implementation for storing newsletter subscription
        // This would integrate with your database
        error_log("Newsletter subscription: $email");

        // TODO: Implement actual database storage
        // You can create a newsletter_subscriptions table and store the email
    }
}
