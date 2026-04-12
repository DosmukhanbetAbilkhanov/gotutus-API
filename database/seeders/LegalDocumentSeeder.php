<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\LegalPage;
use Illuminate\Database\Seeder;

/**
 * Seeds both Privacy Policy and Public Offer legal documents.
 * Idempotent — creates or updates existing documents.
 *
 * Usage: php artisan db:seed --class=LegalDocumentSeeder
 */
class LegalDocumentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPrivacyPolicy();
        $this->seedPublicOffer();
    }

    // ─── Privacy Policy ──────────────────────────────────────────────

    private function seedPrivacyPolicy(): void
    {
        $translations = [
            'en' => ['title' => 'Privacy Policy', 'content' => $this->privacyEn()],
            'ru' => ['title' => 'Политика конфиденциальности', 'content' => $this->privacyRu()],
            'kk' => ['title' => 'Құпиялылық саясаты', 'content' => $this->privacyKk()],
        ];

        $this->upsertLegalPage(LegalPage::SLUG_PRIVACY_POLICY, '1.0', $translations);
        $this->command?->info('Privacy Policy seeded.');
    }

    // ─── Public Offer ────────────────────────────────────────────────

    private function seedPublicOffer(): void
    {
        $translations = [
            'en' => ['title' => 'Public Offer Agreement', 'content' => $this->offerEn()],
            'ru' => ['title' => 'Публичная оферта', 'content' => $this->offerRu()],
            'kk' => ['title' => 'Жария оферта', 'content' => $this->offerKk()],
        ];

        $this->upsertLegalPage(LegalPage::SLUG_PUBLIC_OFFER, '1.0', $translations);
        $this->command?->info('Public Offer seeded.');
    }

    // ═════════════════════════════════════════════════════════════════
    //  PRIVACY POLICY CONTENT
    // ═════════════════════════════════════════════════════════════════

    /**
     * Create or update a legal page and its translations.
     */
    private function upsertLegalPage(string $slug, string $version, array $translations): void
    {
        $page = LegalPage::where('slug', $slug)->first();

        if ($page) {
            $page->update([
                'version' => $version,
                'is_active' => true,
                'published_at' => $page->published_at ?? now(),
            ]);

            foreach ($translations as $lang => $data) {
                $page->translations()->updateOrCreate(
                    ['language_code' => $lang],
                    ['title' => $data['title'], 'content' => $data['content']],
                );
            }
        } else {
            $page = LegalPage::create([
                'slug' => $slug,
                'version' => $version,
                'is_active' => true,
                'published_at' => now(),
            ]);

            foreach ($translations as $lang => $data) {
                $page->translations()->create([
                    'language_code' => $lang,
                    'title' => $data['title'],
                    'content' => $data['content'],
                ]);
            }
        }
    }

    private function privacyEn(): string
    {
        return <<<'HTML'
<h2>1. Introduction</h2>
<p>Welcome to Tanys ("we", "our", "us"). This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our mobile application Tanys (the "App"). Please read this policy carefully. By using the App, you consent to the practices described in this Privacy Policy.</p>

<h2>2. Information We Collect</h2>

<h3>2.1 Personal Information You Provide</h3>
<p>When you register and use the App, we may collect the following personal information:</p>
<ul>
<li><strong>Phone number</strong> — used for account registration, authentication, and account recovery.</li>
<li><strong>Name</strong> — displayed on your profile and visible to other users.</li>
<li><strong>Age</strong> — used for age verification and matching purposes.</li>
<li><strong>Gender</strong> — used for profile display and filtering.</li>
<li><strong>City</strong> — used to show relevant hangouts in your area.</li>
<li><strong>Email address</strong> — used for account recovery and important notifications.</li>
<li><strong>Profile photos</strong> — uploaded voluntarily and visible to other users after moderation.</li>
<li><strong>Bio/description</strong> — optional text about yourself visible on your profile.</li>
</ul>

<h3>2.2 Information Generated Through Use</h3>
<ul>
<li><strong>Messages</strong> — text messages exchanged with other users through in-app chat.</li>
<li><strong>Hangout data</strong> — hangouts you create or join, including activity type, date, time, place, and description.</li>
<li><strong>Ratings and feedback</strong> — ratings you give to other users and places after hangouts.</li>
<li><strong>Reports and complaints</strong> — information submitted when reporting users or filing complaints about places.</li>
</ul>

<h3>2.3 Automatically Collected Information</h3>
<ul>
<li><strong>Device information</strong> — device type, operating system version, unique device identifiers.</li>
<li><strong>Usage data</strong> — app interaction data, crash reports, performance metrics.</li>
<li><strong>Push notification tokens</strong> — used to deliver notifications to your device.</li>
</ul>

<h2>3. How We Use Your Information</h2>
<p>We use the collected information for the following purposes:</p>
<ul>
<li><strong>Providing services</strong> — to create and manage your account, display your profile, enable hangout creation and joining, and facilitate in-app messaging.</li>
<li><strong>Matching and discovery</strong> — to show relevant hangouts based on your city, preferences, and activity interests.</li>
<li><strong>Notifications</strong> — to send push notifications about join requests, approvals, new messages, and other relevant activity.</li>
<li><strong>Safety and moderation</strong> — to review reported content, moderate user photos, and enforce our terms of use.</li>
<li><strong>Improvement</strong> — to analyze usage patterns, fix bugs, improve app performance, and develop new features.</li>
<li><strong>Communication</strong> — to contact you regarding account issues, policy updates, or support requests.</li>
</ul>

<h2>4. Third-Party Services</h2>
<p>We use the following third-party services that may collect information:</p>
<ul>
<li><strong>Firebase Analytics</strong> — to understand app usage patterns and user behavior. Data is collected anonymously and in aggregate.</li>
<li><strong>Firebase Crashlytics</strong> — to collect crash reports and diagnostic information to improve app stability.</li>
<li><strong>Firebase Cloud Messaging (FCM)</strong> — to deliver push notifications to your device.</li>
</ul>
<p>These services have their own privacy policies. We encourage you to review Google's privacy policy at <a href="https://policies.google.com/privacy">https://policies.google.com/privacy</a>.</p>

<h2>5. Data Sharing and Disclosure</h2>
<p>We do not sell your personal information. We may share your information in the following circumstances:</p>
<ul>
<li><strong>With other users</strong> — your name, age, gender, city, bio, photos, and hangout activity are visible to other App users as part of the service functionality.</li>
<li><strong>With service providers</strong> — we may share data with third-party service providers who help us operate and improve the App.</li>
<li><strong>For legal reasons</strong> — we may disclose information if required by law, regulation, legal process, or governmental request.</li>
<li><strong>For safety</strong> — we may share information to protect the rights, safety, and property of our users and the public.</li>
</ul>

<h2>6. Data Retention</h2>
<p>We retain your personal information for as long as your account is active or as needed to provide you services. If you delete your account, we will delete your personal data within 30 days, except where we are required to retain it for legal or legitimate business purposes.</p>

<h2>7. Account Deletion</h2>
<p>You may request deletion of your account and associated data at any time by contacting us at <a href="mailto:administrator@tanys.app">administrator@tanys.app</a>. Upon receiving your request, we will:</p>
<ul>
<li>Delete your profile information, photos, and personal data.</li>
<li>Remove your messages from active conversations.</li>
<li>Cancel any active hangouts you have created.</li>
<li>Process the deletion within 30 days.</li>
</ul>

<h2>8. Children's Privacy</h2>
<p>The App is not intended for users under the age of 16. We do not knowingly collect personal information from children under 16. If we become aware that we have collected personal data from a child under 16, we will take steps to delete that information promptly.</p>

<h2>9. Your Rights</h2>
<p>Depending on your jurisdiction, you may have the following rights regarding your personal data:</p>
<ul>
<li><strong>Access</strong> — you can request a copy of the personal data we hold about you.</li>
<li><strong>Correction</strong> — you can update your profile information directly in the App.</li>
<li><strong>Deletion</strong> — you can request deletion of your account and personal data.</li>
<li><strong>Withdraw consent</strong> — you can withdraw your consent to data processing at any time by deleting your account.</li>
<li><strong>Data portability</strong> — you can request your data in a structured, machine-readable format.</li>
</ul>
<p>To exercise any of these rights, please contact us at <a href="mailto:administrator@tanys.app">administrator@tanys.app</a>.</p>

<h2>10. Data Security</h2>
<p>We implement appropriate technical and organizational measures to protect your personal data against unauthorized access, alteration, disclosure, or destruction. These measures include:</p>
<ul>
<li>Encrypted data transmission (HTTPS/TLS).</li>
<li>Secure token-based authentication.</li>
<li>Regular security audits and updates.</li>
<li>Photo moderation before public display.</li>
</ul>
<p>However, no method of electronic transmission or storage is 100% secure. We cannot guarantee absolute security of your data.</p>

<h2>11. Changes to This Policy</h2>
<p>We may update this Privacy Policy from time to time. We will notify you of any material changes by posting the new policy in the App and updating the "Last updated" date. Your continued use of the App after changes constitutes acceptance of the updated policy.</p>

<h2>12. Contact Us</h2>
<p>If you have any questions or concerns about this Privacy Policy or our data practices, please contact us:</p>
<ul>
<li><strong>Company</strong>: TOO AMIR TRADE (Amir Trade LLP)</li>
<li><strong>Email</strong>: <a href="mailto:administrator@tanys.app">administrator@tanys.app</a></li>
</ul>
HTML;
    }

    private function privacyRu(): string
    {
        return <<<'HTML'
<h2>1. Введение</h2>
<p>Добро пожаловать в Tanys («мы», «наш», «нас»). Настоящая Политика конфиденциальности объясняет, как мы собираем, используем, раскрываем и защищаем вашу информацию при использовании мобильного приложения Tanys («Приложение»). Пожалуйста, внимательно прочитайте эту политику. Используя Приложение, вы соглашаетесь с практиками, описанными в настоящей Политике конфиденциальности.</p>

<h2>2. Информация, которую мы собираем</h2>

<h3>2.1 Персональная информация, которую вы предоставляете</h3>
<p>При регистрации и использовании Приложения мы можем собирать следующую персональную информацию:</p>
<ul>
<li><strong>Номер телефона</strong> — используется для регистрации аккаунта, аутентификации и восстановления аккаунта.</li>
<li><strong>Имя</strong> — отображается в вашем профиле и видно другим пользователям.</li>
<li><strong>Возраст</strong> — используется для проверки возраста и подбора.</li>
<li><strong>Пол</strong> — используется для отображения профиля и фильтрации.</li>
<li><strong>Город</strong> — используется для показа актуальных встреч в вашем районе.</li>
<li><strong>Адрес электронной почты</strong> — используется для восстановления аккаунта и важных уведомлений.</li>
<li><strong>Фотографии профиля</strong> — загружаются добровольно и видны другим пользователям после модерации.</li>
<li><strong>Описание/био</strong> — необязательный текст о себе, видимый в профиле.</li>
</ul>

<h3>2.2 Информация, генерируемая при использовании</h3>
<ul>
<li><strong>Сообщения</strong> — текстовые сообщения, которыми вы обмениваетесь с другими пользователями через чат в приложении.</li>
<li><strong>Данные о встречах</strong> — встречи, которые вы создаёте или к которым присоединяетесь, включая тип активности, дату, время, место и описание.</li>
<li><strong>Оценки и отзывы</strong> — оценки, которые вы ставите другим пользователям и заведениям после встреч.</li>
<li><strong>Жалобы и отчёты</strong> — информация, отправленная при жалобе на пользователей или подаче жалоб на заведения.</li>
</ul>

<h3>2.3 Автоматически собираемая информация</h3>
<ul>
<li><strong>Информация об устройстве</strong> — тип устройства, версия операционной системы, уникальные идентификаторы устройства.</li>
<li><strong>Данные об использовании</strong> — данные о взаимодействии с приложением, отчёты о сбоях, метрики производительности.</li>
<li><strong>Токены push-уведомлений</strong> — используются для доставки уведомлений на ваше устройство.</li>
</ul>

<h2>3. Как мы используем вашу информацию</h2>
<p>Мы используем собранную информацию для следующих целей:</p>
<ul>
<li><strong>Предоставление услуг</strong> — для создания и управления вашим аккаунтом, отображения профиля, обеспечения создания встреч и присоединения к ним, а также для обмена сообщениями в приложении.</li>
<li><strong>Подбор и обнаружение</strong> — для показа актуальных встреч на основе вашего города, предпочтений и интересов.</li>
<li><strong>Уведомления</strong> — для отправки push-уведомлений о заявках, одобрениях, новых сообщениях и другой активности.</li>
<li><strong>Безопасность и модерация</strong> — для проверки жалоб, модерации фотографий пользователей и соблюдения условий использования.</li>
<li><strong>Улучшение</strong> — для анализа паттернов использования, исправления ошибок, улучшения производительности и разработки новых функций.</li>
<li><strong>Коммуникация</strong> — для связи с вами по вопросам аккаунта, обновлений политик или запросов поддержки.</li>
</ul>

<h2>4. Сторонние сервисы</h2>
<p>Мы используем следующие сторонние сервисы, которые могут собирать информацию:</p>
<ul>
<li><strong>Firebase Analytics</strong> — для понимания паттернов использования приложения и поведения пользователей. Данные собираются анонимно и в агрегированном виде.</li>
<li><strong>Firebase Crashlytics</strong> — для сбора отчётов о сбоях и диагностической информации для улучшения стабильности приложения.</li>
<li><strong>Firebase Cloud Messaging (FCM)</strong> — для доставки push-уведомлений на ваше устройство.</li>
</ul>
<p>Эти сервисы имеют собственные политики конфиденциальности. Мы рекомендуем ознакомиться с политикой конфиденциальности Google по адресу <a href="https://policies.google.com/privacy">https://policies.google.com/privacy</a>.</p>

<h2>5. Передача и раскрытие данных</h2>
<p>Мы не продаём вашу персональную информацию. Мы можем передавать вашу информацию в следующих случаях:</p>
<ul>
<li><strong>Другим пользователям</strong> — ваше имя, возраст, пол, город, описание, фотографии и активность встреч видны другим пользователям Приложения как часть функциональности сервиса.</li>
<li><strong>Поставщикам услуг</strong> — мы можем передавать данные сторонним поставщикам услуг, которые помогают нам управлять и улучшать Приложение.</li>
<li><strong>По правовым основаниям</strong> — мы можем раскрывать информацию, если это требуется законом, нормативными актами, судебным процессом или запросом государственных органов.</li>
<li><strong>Для безопасности</strong> — мы можем передавать информацию для защиты прав, безопасности и собственности наших пользователей и общественности.</li>
</ul>

<h2>6. Хранение данных</h2>
<p>Мы храним вашу персональную информацию до тех пор, пока ваш аккаунт активен или это необходимо для предоставления вам услуг. Если вы удалите свой аккаунт, мы удалим ваши персональные данные в течение 30 дней, за исключением случаев, когда мы обязаны хранить их по юридическим или законным деловым причинам.</p>

<h2>7. Удаление аккаунта</h2>
<p>Вы можете запросить удаление своего аккаунта и связанных данных в любое время, связавшись с нами по адресу <a href="mailto:administrator@tanys.app">administrator@tanys.app</a>. При получении вашего запроса мы:</p>
<ul>
<li>Удалим информацию вашего профиля, фотографии и персональные данные.</li>
<li>Удалим ваши сообщения из активных переписок.</li>
<li>Отменим все активные встречи, которые вы создали.</li>
<li>Обработаем удаление в течение 30 дней.</li>
</ul>

<h2>8. Конфиденциальность детей</h2>
<p>Приложение не предназначено для пользователей младше 16 лет. Мы сознательно не собираем персональную информацию от детей младше 16 лет. Если нам станет известно, что мы собрали персональные данные ребёнка младше 16 лет, мы примем меры для незамедлительного удаления этой информации.</p>

<h2>9. Ваши права</h2>
<p>В зависимости от вашей юрисдикции вы можете иметь следующие права в отношении ваших персональных данных:</p>
<ul>
<li><strong>Доступ</strong> — вы можете запросить копию персональных данных, которые мы храним о вас.</li>
<li><strong>Исправление</strong> — вы можете обновить информацию профиля непосредственно в Приложении.</li>
<li><strong>Удаление</strong> — вы можете запросить удаление вашего аккаунта и персональных данных.</li>
<li><strong>Отзыв согласия</strong> — вы можете отозвать своё согласие на обработку данных в любое время, удалив свой аккаунт.</li>
<li><strong>Переносимость данных</strong> — вы можете запросить ваши данные в структурированном, машиночитаемом формате.</li>
</ul>
<p>Для осуществления любого из этих прав, пожалуйста, свяжитесь с нами по адресу <a href="mailto:administrator@tanys.app">administrator@tanys.app</a>.</p>

<h2>10. Безопасность данных</h2>
<p>Мы применяем соответствующие технические и организационные меры для защиты ваших персональных данных от несанкционированного доступа, изменения, раскрытия или уничтожения. Эти меры включают:</p>
<ul>
<li>Шифрование передачи данных (HTTPS/TLS).</li>
<li>Безопасная аутентификация на основе токенов.</li>
<li>Регулярные аудиты безопасности и обновления.</li>
<li>Модерация фотографий перед публичным отображением.</li>
</ul>
<p>Однако ни один метод электронной передачи или хранения не является на 100% безопасным. Мы не можем гарантировать абсолютную безопасность ваших данных.</p>

<h2>11. Изменения в этой политике</h2>
<p>Мы можем обновлять настоящую Политику конфиденциальности время от времени. Мы уведомим вас о любых существенных изменениях, опубликовав новую политику в Приложении и обновив дату «Последнее обновление». Продолжение использования Приложения после изменений означает принятие обновлённой политики.</p>

<h2>12. Свяжитесь с нами</h2>
<p>Если у вас есть вопросы или замечания по поводу настоящей Политики конфиденциальности или наших практик обработки данных, пожалуйста, свяжитесь с нами:</p>
<ul>
<li><strong>Компания</strong>: ТОО AMIR TRADE (Amir Trade LLP)</li>
<li><strong>Email</strong>: <a href="mailto:administrator@tanys.app">administrator@tanys.app</a></li>
</ul>
HTML;
    }

    private function privacyKk(): string
    {
        return <<<'HTML'
<h2>1. Кіріспе</h2>
<p>Tanys-қа қош келдіңіз («біз», «біздің»). Бұл Құпиялылық саясаты Tanys мобильді қосымшасын («Қосымша») пайдаланған кезде біз сіздің ақпаратыңызды қалай жинайтынымызды, пайдаланатынымызды, ашатынымызды және қорғайтынымызды түсіндіреді. Бұл саясатты мұқият оқыңыз. Қосымшаны пайдалана отырып, сіз осы Құпиялылық саясатында сипатталған тәжірибелерге келісесіз.</p>

<h2>2. Біз жинайтын ақпарат</h2>

<h3>2.1 Сіз беретін жеке ақпарат</h3>
<p>Тіркелу және Қосымшаны пайдалану кезінде біз келесі жеке ақпаратты жинай аламыз:</p>
<ul>
<li><strong>Телефон нөмірі</strong> — тіркелгіні тіркеу, аутентификация және тіркелгіні қалпына келтіру үшін пайдаланылады.</li>
<li><strong>Аты</strong> — профильде көрсетіледі және басқа пайдаланушыларға көрінеді.</li>
<li><strong>Жасы</strong> — жасты тексеру және сәйкестендіру мақсатында пайдаланылады.</li>
<li><strong>Жынысы</strong> — профильді көрсету және сүзу үшін пайдаланылады.</li>
<li><strong>Қала</strong> — сіздің аймағыңыздағы тиісті кездесулерді көрсету үшін пайдаланылады.</li>
<li><strong>Электрондық пошта мекенжайы</strong> — тіркелгіні қалпына келтіру және маңызды хабарландырулар үшін пайдаланылады.</li>
<li><strong>Профиль фотосуреттері</strong> — ерікті түрде жүктеледі және модерациядан кейін басқа пайдаланушыларға көрінеді.</li>
<li><strong>Био/сипаттама</strong> — профильде көрінетін өзіңіз туралы міндетті емес мәтін.</li>
</ul>

<h3>2.2 Пайдалану барысында жасалатын ақпарат</h3>
<ul>
<li><strong>Хабарламалар</strong> — қосымша ішіндегі чат арқылы басқа пайдаланушылармен алмасатын мәтіндік хабарламалар.</li>
<li><strong>Кездесу деректері</strong> — сіз жасайтын немесе қосылатын кездесулер, оның ішінде белсенділік түрі, күні, уақыты, орны және сипаттамасы.</li>
<li><strong>Бағалар мен пікірлер</strong> — кездесулерден кейін басқа пайдаланушылар мен орындарға қоятын бағаларыңыз.</li>
<li><strong>Шағымдар мен есептер</strong> — пайдаланушыларға шағымданғанда немесе орындар туралы шағым бергенде жіберілетін ақпарат.</li>
</ul>

<h3>2.3 Автоматты түрде жиналатын ақпарат</h3>
<ul>
<li><strong>Құрылғы ақпараты</strong> — құрылғы түрі, операциялық жүйе нұсқасы, бірегей құрылғы идентификаторлары.</li>
<li><strong>Пайдалану деректері</strong> — қосымшамен өзара әрекеттесу деректері, бұзылу есептері, өнімділік көрсеткіштері.</li>
<li><strong>Push-хабарландыру токендері</strong> — құрылғыңызға хабарландыруларды жеткізу үшін пайдаланылады.</li>
</ul>

<h2>3. Ақпаратыңызды қалай пайдаланамыз</h2>
<p>Жиналған ақпаратты келесі мақсаттарда пайдаланамыз:</p>
<ul>
<li><strong>Қызмет көрсету</strong> — тіркелгіңізді жасау және басқару, профиліңізді көрсету, кездесулер жасауды және қосылуды қамтамасыз ету, қосымша ішінде хабар алмасуды жеңілдету.</li>
<li><strong>Сәйкестендіру және табу</strong> — қалаңызға, қалауларыңызға және белсенділік қызығушылықтарыңызға негізделген тиісті кездесулерді көрсету.</li>
<li><strong>Хабарландырулар</strong> — қосылу сұраныстары, мақұлдаулар, жаңа хабарламалар және басқа тиісті белсенділік туралы push-хабарландырулар жіберу.</li>
<li><strong>Қауіпсіздік және модерация</strong> — шағымдарды тексеру, пайдаланушы фотосуреттерін модерациялау және пайдалану шарттарын сақтау.</li>
<li><strong>Жақсарту</strong> — пайдалану үлгілерін талдау, қателерді түзету, қосымша өнімділігін жақсарту және жаңа мүмкіндіктер әзірлеу.</li>
<li><strong>Байланыс</strong> — тіркелгі мәселелері, саясат жаңартулары немесе қолдау сұраныстары бойынша сізбен байланысу.</li>
</ul>

<h2>4. Үшінші тарап қызметтері</h2>
<p>Біз ақпарат жинай алатын келесі үшінші тарап қызметтерін пайдаланамыз:</p>
<ul>
<li><strong>Firebase Analytics</strong> — қосымшаны пайдалану үлгілері мен пайдаланушы мінез-құлқын түсіну үшін. Деректер анонимді және жинақталған түрде жиналады.</li>
<li><strong>Firebase Crashlytics</strong> — қосымша тұрақтылығын жақсарту үшін бұзылу есептері мен диагностикалық ақпаратты жинау.</li>
<li><strong>Firebase Cloud Messaging (FCM)</strong> — құрылғыңызға push-хабарландыруларды жеткізу үшін.</li>
</ul>
<p>Бұл қызметтердің өздерінің құпиялылық саясаттары бар. Google құпиялылық саясатымен <a href="https://policies.google.com/privacy">https://policies.google.com/privacy</a> мекенжайында танысуды ұсынамыз.</p>

<h2>5. Деректерді бөлісу және ашу</h2>
<p>Біз сіздің жеке ақпаратыңызды сатпаймыз. Ақпаратыңызды келесі жағдайларда бөлісе аламыз:</p>
<ul>
<li><strong>Басқа пайдаланушылармен</strong> — атыңыз, жасыңыз, жынысыңыз, қалаңыз, сипаттамаңыз, фотосуреттеріңіз және кездесу белсенділігіңіз сервис функционалдығының бөлігі ретінде басқа Қосымша пайдаланушыларына көрінеді.</li>
<li><strong>Қызмет көрсетушілермен</strong> — Қосымшаны басқаруға және жақсартуға көмектесетін үшінші тарап қызмет көрсетушілерімен деректерді бөлісе аламыз.</li>
<li><strong>Заңды себептермен</strong> — заң, нормативтік актілер, сот процесі немесе мемлекеттік органдардың сұранысы бойынша талап етілсе, ақпаратты аша аламыз.</li>
<li><strong>Қауіпсіздік үшін</strong> — пайдаланушыларымыз бен қоғамның құқықтарын, қауіпсіздігін және меншігін қорғау үшін ақпаратты бөлісе аламыз.</li>
</ul>

<h2>6. Деректерді сақтау</h2>
<p>Біз сіздің жеке ақпаратыңызды тіркелгіңіз белсенді болғанша немесе қызмет көрсету үшін қажет болғанша сақтаймыз. Тіркелгіңізді жойсаңыз, жеке деректеріңізді 30 күн ішінде жоямыз, заңды немесе заңды іскерлік мақсаттар үшін сақтауға міндетті болған жағдайларды қоспағанда.</p>

<h2>7. Тіркелгіні жою</h2>
<p>Тіркелгіңіз бен байланысты деректеріңізді жоюды кез келген уақытта <a href="mailto:administrator@tanys.app">administrator@tanys.app</a> мекенжайына хабарласу арқылы сұрай аласыз. Сұранысыңызды алғаннан кейін біз:</p>
<ul>
<li>Профиль ақпаратыңызды, фотосуреттеріңізді және жеке деректеріңізді жоямыз.</li>
<li>Белсенді сөйлесулердегі хабарламаларыңызды жоямыз.</li>
<li>Сіз жасаған барлық белсенді кездесулерді болдырмаймыз.</li>
<li>Жоюды 30 күн ішінде өңдейміз.</li>
</ul>

<h2>8. Балалардың құпиялылығы</h2>
<p>Қосымша 16 жасқа толмаған пайдаланушыларға арналмаған. Біз 16 жасқа толмаған балалардан жеке ақпаратты білместен жинамаймыз. Егер біз 16 жасқа толмаған баладан жеке деректер жинағанымызды білсек, бұл ақпаратты дереу жою шараларын қолданамыз.</p>

<h2>9. Сіздің құқықтарыңыз</h2>
<p>Юрисдикцияңызға байланысты жеке деректеріңізге қатысты келесі құқықтарыңыз болуы мүмкін:</p>
<ul>
<li><strong>Қол жеткізу</strong> — сіз туралы сақтайтын жеке деректердің көшірмесін сұрай аласыз.</li>
<li><strong>Түзету</strong> — профиль ақпаратыңызды тікелей Қосымшада жаңарта аласыз.</li>
<li><strong>Жою</strong> — тіркелгіңіз бен жеке деректеріңізді жоюды сұрай аласыз.</li>
<li><strong>Келісімді қайтарып алу</strong> — тіркелгіңізді жою арқылы деректерді өңдеуге берген келісіміңізді кез келген уақытта қайтарып ала аласыз.</li>
<li><strong>Деректерді тасымалдау</strong> — деректеріңізді құрылымдалған, машинада оқылатын форматта сұрай аласыз.</li>
</ul>
<p>Осы құқықтардың кез келгенін жүзеге асыру үшін бізге <a href="mailto:administrator@tanys.app">administrator@tanys.app</a> мекенжайы арқылы хабарласыңыз.</p>

<h2>10. Деректер қауіпсіздігі</h2>
<p>Біз жеке деректеріңізді рұқсатсыз қол жеткізу, өзгерту, ашу немесе жоюдан қорғау үшін тиісті техникалық және ұйымдастырушылық шараларды қолданамыз. Бұл шараларға мыналар кіреді:</p>
<ul>
<li>Деректер тасымалын шифрлау (HTTPS/TLS).</li>
<li>Токенге негізделген қауіпсіз аутентификация.</li>
<li>Тұрақты қауіпсіздік аудиттері мен жаңартулар.</li>
<li>Жария көрсету алдында фотосуреттерді модерациялау.</li>
</ul>
<p>Алайда, электронды тасымалдау немесе сақтаудың ешбір әдісі 100% қауіпсіз емес. Біз деректеріңіздің абсолютті қауіпсіздігіне кепілдік бере алмаймыз.</p>

<h2>11. Осы саясатқа өзгерістер</h2>
<p>Біз осы Құпиялылық саясатын мезгіл-мезгіл жаңартып отыра аламыз. Кез келген елеулі өзгерістер туралы Қосымшада жаңа саясатты жариялау және «Соңғы жаңарту» күнін жаңарту арқылы хабарлаймыз. Өзгерістерден кейін Қосымшаны пайдалануды жалғастыру жаңартылған саясатты қабылдауды білдіреді.</p>

<h2>12. Бізбен байланыс</h2>
<p>Осы Құпиялылық саясаты немесе деректерді өңдеу тәжірибелеріміз бойынша сұрақтарыңыз немесе мазасызданушылықтарыңыз болса, бізбен байланысыңыз:</p>
<ul>
<li><strong>Компания</strong>: AMIR TRADE ЖШС (Amir Trade LLP)</li>
<li><strong>Email</strong>: <a href="mailto:administrator@tanys.app">administrator@tanys.app</a></li>
</ul>
HTML;
    }

    // ═════════════════════════════════════════════════════════════════
    //  PUBLIC OFFER CONTENT
    // ═════════════════════════════════════════════════════════════════

    private function offerEn(): string
    {
        return <<<'HTML'
<h2>1. General Provisions</h2>
<p>This Public Offer Agreement (hereinafter — the "Agreement") is an official offer by the administration of the Tanys mobile application (hereinafter — the "Service Provider") addressed to any individual (hereinafter — the "User") to enter into an agreement for the use of the Tanys mobile application (hereinafter — the "App") on the terms set forth below.</p>
<p>In accordance with the legislation of the Republic of Kazakhstan, this document is a public offer, and by accepting the terms below (registration in the App), you enter into this Agreement on the terms of adhesion.</p>

<h2>2. Terms and Definitions</h2>
<ul>
<li><strong>App</strong> — the Tanys mobile application available on iOS and Android platforms, designed for organizing and finding social hangouts.</li>
<li><strong>User</strong> — an individual aged 16 years or older who has registered in the App and accepted the terms of this Agreement.</li>
<li><strong>Hangout</strong> — a social event created by a User through the App, specifying an activity type, date, time, place, and description.</li>
<li><strong>Profile</strong> — the User's personal page in the App containing their name, age, gender, city, photos, bio, and other information.</li>
<li><strong>Content</strong> — any information, text, photos, messages, and other materials that the User publishes or transmits through the App.</li>
<li><strong>Moderation</strong> — the process of reviewing User Content by the Service Provider to ensure compliance with this Agreement and applicable law.</li>
</ul>

<h2>3. Subject of the Agreement</h2>
<p>3.1. The Service Provider grants the User a non-exclusive, non-transferable right to use the App for its intended purpose: creating and joining social hangouts, communicating with other Users, discovering places, and viewing promotions.</p>
<p>3.2. The App is provided on an "as is" basis. The Service Provider does not guarantee uninterrupted or error-free operation of the App.</p>
<p>3.3. The use of the App is free of charge. The Service Provider reserves the right to introduce paid features in the future with prior notice to Users.</p>

<h2>4. Registration and Account</h2>
<p>4.1. To use the App, the User must register by providing a valid phone number, name, age, gender, email address, and selecting a city.</p>
<p>4.2. The User confirms that all information provided during registration is accurate and up to date. The User is responsible for keeping this information current.</p>
<p>4.3. The User is solely responsible for maintaining the confidentiality of their account credentials and for all activity performed under their account.</p>
<p>4.4. One individual may maintain only one account in the App. Creating multiple accounts may result in account suspension.</p>

<h2>5. User Rights and Obligations</h2>

<h3>5.1. The User has the right to:</h3>
<ul>
<li>Create, edit, and delete hangouts.</li>
<li>Join hangouts created by other Users by sending a join request.</li>
<li>Communicate with other Users through in-app chat after mutual participation in a hangout or approval of a join request.</li>
<li>Upload and manage profile photos (subject to moderation).</li>
<li>Rate other Users and places after hangouts.</li>
<li>Report violations by other Users.</li>
<li>Request deletion of their account and personal data.</li>
</ul>

<h3>5.2. The User is obligated to:</h3>
<ul>
<li>Comply with the terms of this Agreement and the applicable legislation of the Republic of Kazakhstan.</li>
<li>Provide accurate and truthful information in their profile.</li>
<li>Treat other Users with respect and refrain from harassment, discrimination, threats, or abuse.</li>
<li>Not publish illegal, offensive, obscene, or harmful content.</li>
<li>Not use the App for commercial purposes, advertising, or spam without the Service Provider's consent.</li>
<li>Not attempt to gain unauthorized access to the App's systems, other Users' accounts, or backend infrastructure.</li>
<li>Not use automated means (bots, scrapers) to interact with the App.</li>
</ul>

<h2>6. Service Provider Rights and Obligations</h2>

<h3>6.1. The Service Provider has the right to:</h3>
<ul>
<li>Modify, update, or discontinue the App or any of its features at any time without prior notice.</li>
<li>Moderate User Content, including photos, profile information, and messages, and remove any Content that violates this Agreement.</li>
<li>Suspend or permanently block a User's account for violations of this Agreement.</li>
<li>Send notifications to Users regarding App updates, policy changes, and service-related information.</li>
<li>Collect and process anonymized usage data for analytics and improvement purposes.</li>
</ul>

<h3>6.2. The Service Provider is obligated to:</h3>
<ul>
<li>Make reasonable efforts to ensure the availability and proper functioning of the App.</li>
<li>Protect User personal data in accordance with the Privacy Policy and applicable data protection laws.</li>
<li>Process account deletion requests within 30 days.</li>
<li>Notify Users of material changes to this Agreement.</li>
</ul>

<h2>7. Content and Intellectual Property</h2>
<p>7.1. The User retains ownership of the Content they publish in the App. By publishing Content, the User grants the Service Provider a non-exclusive, worldwide, royalty-free license to display, distribute, and process such Content within the App for the purpose of providing services.</p>
<p>7.2. The App, its design, source code, logos, and all related materials are the intellectual property of the Service Provider and are protected by applicable intellectual property laws.</p>
<p>7.3. The User shall not copy, modify, distribute, or create derivative works of the App or any of its components.</p>

<h2>8. Hangouts and User Interactions</h2>
<p>8.1. The Service Provider is a platform that facilitates connections between Users. The Service Provider is not a party to, organizer of, or participant in any hangout. The Service Provider does not act as an agent, intermediary, or representative of any User.</p>
<p>8.2. Users organize and participate in hangouts entirely at their own risk and responsibility. The Service Provider bears no liability whatsoever for any actions, omissions, behavior, statements, or damages arising from User interactions before, during, or after hangouts.</p>
<p>8.3. The Service Provider does not verify the identity, background, criminal history, or intentions of Users beyond the information provided during registration and moderation of photos. The Service Provider makes no representations or warranties regarding the character, reliability, safety, or lawfulness of any User.</p>
<p>8.4. The Service Provider is not responsible for any physical, emotional, psychological, financial, or material harm, injury, loss, or damage that may occur as a result of meetings, hangouts, or any other interactions between Users, whether arranged through the App or otherwise.</p>
<p>8.5. The Service Provider is not responsible for any illegal, unlawful, offensive, threatening, defamatory, or otherwise harmful actions committed by Users, whether during hangouts or through messages, profile content, or any other use of the App.</p>
<p>8.6. Users acknowledge that interacting with other Users, including meeting in person, carries inherent risks. Users are solely responsible for taking appropriate safety precautions when participating in hangouts, including but not limited to choosing public meeting places, informing third parties of their plans, and exercising personal judgment.</p>
<p>8.7. The Service Provider does not endorse, recommend, or guarantee any hangout, activity, venue, or User. Any information displayed in the App regarding places, promotions, or activities is provided for informational purposes only.</p>

<h2>9. User Responsibility and Indemnification</h2>
<p>9.1. The User is fully and solely responsible for their own actions, behavior, and any consequences arising from the use of the App and participation in hangouts.</p>
<p>9.2. The User agrees to comply with all applicable laws and regulations of the Republic of Kazakhstan while using the App and during any hangouts or interactions with other Users.</p>
<p>9.3. The User shall not use the App for any unlawful, fraudulent, malicious, or harmful purposes, including but not limited to:</p>
<ul>
<li>Committing or facilitating criminal offenses.</li>
<li>Harassment, stalking, intimidation, or threats against other Users.</li>
<li>Distributing illegal substances or engaging in illegal trade.</li>
<li>Soliciting money, goods, or services under false pretenses.</li>
<li>Organizing activities that violate public order or morality.</li>
<li>Disseminating extremist, hateful, discriminatory, or violent content.</li>
<li>Impersonating another person or misrepresenting one's identity.</li>
<li>Collecting personal data of other Users for unauthorized purposes.</li>
</ul>
<p>9.4. The User agrees to indemnify, defend, and hold harmless the Service Provider, its owners, directors, employees, agents, and affiliates from and against any and all claims, demands, lawsuits, losses, damages, liabilities, costs, and expenses (including reasonable legal fees) arising out of or related to:</p>
<ul>
<li>The User's use of the App or participation in hangouts.</li>
<li>The User's violation of this Agreement or any applicable law.</li>
<li>The User's Content or information provided through the App.</li>
<li>Any harm, injury, or damage caused by the User to other Users or third parties.</li>
<li>Any dispute between the User and other Users.</li>
</ul>
<p>9.5. The User acknowledges that the Service Provider is not an insurer and does not provide any insurance coverage for hangouts or User interactions. The User is encouraged to obtain their own insurance if they consider it necessary.</p>
<p>9.6. In the event that the Service Provider suffers any losses, damages, fines, or penalties as a result of the User's unlawful or wrongful actions, the User shall compensate the Service Provider in full for all such losses.</p>

<h2>10. Limitation of Liability</h2>
<p>10.1. The Service Provider shall not be liable for:</p>
<ul>
<li>Any direct, indirect, incidental, special, or consequential damages arising from the use of or inability to use the App.</li>
<li>Any harm, injury, loss, or damage arising from meetings, hangouts, or interactions between Users.</li>
<li>Actions, behavior, statements, or omissions of other Users, whether online or offline, including during hangouts.</li>
<li>Any criminal, unlawful, or harmful acts committed by Users.</li>
<li>Accuracy, completeness, truthfulness, or reliability of information provided by Users in their profiles, messages, or hangout descriptions.</li>
<li>The quality, safety, legality, or suitability of any venue, activity, or event associated with hangouts.</li>
<li>Temporary unavailability of the App due to technical maintenance, updates, or force majeure circumstances.</li>
<li>Loss of User data resulting from circumstances beyond the Service Provider's reasonable control.</li>
<li>Any consequences of the User's decision to meet with other Users in person.</li>
</ul>
<p>10.2. The Service Provider provides the App solely as a technological platform for facilitating social connections. The Service Provider does not control and cannot be held responsible for the offline conduct of Users.</p>
<p>10.3. In any case, the total liability of the Service Provider to the User shall not exceed the amount paid by the User for paid services (if any) during the 12 months preceding the claim.</p>
<p>10.4. The User agrees that the limitations of liability set forth in this Agreement are reasonable and form an essential basis of the agreement between the User and the Service Provider.</p>

<h2>11. Blocking and Account Termination</h2>
<p>11.1. The Service Provider may suspend or terminate a User's account without prior notice if the User:</p>
<ul>
<li>Violates the terms of this Agreement.</li>
<li>Publishes prohibited Content.</li>
<li>Engages in harassment, threats, or abusive behavior toward other Users.</li>
<li>Uses the App for fraudulent or illegal purposes.</li>
<li>Creates multiple accounts.</li>
<li>Causes harm, injury, or damage to other Users or third parties during hangouts or through the App.</li>
</ul>
<p>11.2. A blocked User may appeal the decision by contacting the Service Provider at <a href="mailto:administrator@tanys.app">administrator@tanys.app</a>.</p>
<p>11.3. The Service Provider reserves the right to report any illegal activity discovered through the App to the relevant law enforcement authorities.</p>

<h2>12. Dispute Resolution</h2>
<p>12.1. All disputes arising from this Agreement shall be resolved through negotiation. If the parties cannot reach an agreement, disputes shall be resolved in accordance with the legislation of the Republic of Kazakhstan.</p>
<p>12.2. The User may submit complaints or claims to <a href="mailto:administrator@tanys.app">administrator@tanys.app</a>. The Service Provider shall respond within 15 business days.</p>
<p>12.3. Any disputes between Users arising from hangouts or interactions are resolved exclusively between the Users involved. The Service Provider is not a party to such disputes and bears no obligation to mediate or resolve them.</p>

<h2>13. Amendments to the Agreement</h2>
<p>13.1. The Service Provider reserves the right to modify this Agreement at any time. Changes take effect upon publication of the updated Agreement in the App.</p>
<p>13.2. The User will be notified of material changes through an in-app notification or via email. Continued use of the App after the effective date of changes constitutes acceptance of the updated terms.</p>
<p>13.3. If the User does not agree with the changes, they must stop using the App and may request deletion of their account.</p>

<h2>14. Final Provisions</h2>
<p>14.1. This Agreement is governed by the laws of the Republic of Kazakhstan.</p>
<p>14.2. If any provision of this Agreement is found to be invalid or unenforceable, the remaining provisions shall remain in full force and effect.</p>
<p>14.3. This Agreement constitutes the entire agreement between the User and the Service Provider regarding the use of the App.</p>
<p>14.4. The acceptance of this Agreement (by registering in the App) is equivalent to the conclusion of a written agreement.</p>
<p>14.5. By accepting this Agreement, the User confirms that they have read, understood, and agree to be bound by all provisions, including limitations of liability, indemnification obligations, and the acknowledgment of risks associated with meeting other Users.</p>

<h2>15. Contact Information</h2>
<p>For questions regarding this Agreement, please contact:</p>
<ul>
<li><strong>Company</strong>: TOO AMIR TRADE (Amir Trade LLP)</li>
<li><strong>Email</strong>: <a href="mailto:administrator@tanys.app">administrator@tanys.app</a></li>
</ul>
HTML;
    }

    private function offerRu(): string
    {
        return <<<'HTML'
<h2>1. Общие положения</h2>
<p>Настоящий договор публичной оферты (далее — «Соглашение») является официальным предложением администрации мобильного приложения Tanys (далее — «Исполнитель»), адресованным любому физическому лицу (далее — «Пользователь»), заключить договор на использование мобильного приложения Tanys (далее — «Приложение») на изложенных ниже условиях.</p>
<p>В соответствии с законодательством Республики Казахстан настоящий документ является публичной офертой, и принятие нижеизложенных условий (регистрация в Приложении) означает заключение настоящего Соглашения на условиях присоединения.</p>

<h2>2. Термины и определения</h2>
<ul>
<li><strong>Приложение</strong> — мобильное приложение Tanys, доступное на платформах iOS и Android, предназначенное для организации и поиска совместных встреч.</li>
<li><strong>Пользователь</strong> — физическое лицо в возрасте от 16 лет, зарегистрировавшееся в Приложении и принявшее условия настоящего Соглашения.</li>
<li><strong>Встреча</strong> — социальное мероприятие, создаваемое Пользователем через Приложение с указанием типа активности, даты, времени, места и описания.</li>
<li><strong>Профиль</strong> — личная страница Пользователя в Приложении, содержащая имя, возраст, пол, город, фотографии, описание и другую информацию.</li>
<li><strong>Контент</strong> — любая информация, тексты, фотографии, сообщения и другие материалы, которые Пользователь публикует или передаёт через Приложение.</li>
<li><strong>Модерация</strong> — процесс проверки Контента Пользователей Исполнителем на предмет соответствия настоящему Соглашению и действующему законодательству.</li>
</ul>

<h2>3. Предмет Соглашения</h2>
<p>3.1. Исполнитель предоставляет Пользователю неисключительное, непередаваемое право на использование Приложения по его назначению: создание и участие в совместных встречах, общение с другими Пользователями, поиск заведений и просмотр акций.</p>
<p>3.2. Приложение предоставляется на условиях «как есть». Исполнитель не гарантирует бесперебойную и безошибочную работу Приложения.</p>
<p>3.3. Использование Приложения является бесплатным. Исполнитель оставляет за собой право ввести платные функции в будущем с предварительным уведомлением Пользователей.</p>

<h2>4. Регистрация и учётная запись</h2>
<p>4.1. Для использования Приложения Пользователь должен зарегистрироваться, указав действующий номер телефона, имя, возраст, пол, адрес электронной почты и выбрав город.</p>
<p>4.2. Пользователь подтверждает, что вся предоставленная при регистрации информация является достоверной и актуальной. Пользователь обязуется поддерживать актуальность данной информации.</p>
<p>4.3. Пользователь несёт единоличную ответственность за сохранение конфиденциальности учётных данных своего аккаунта и за все действия, совершённые под его учётной записью.</p>
<p>4.4. Одно физическое лицо может иметь только одну учётную запись в Приложении. Создание нескольких аккаунтов может привести к блокировке учётной записи.</p>

<h2>5. Права и обязанности Пользователя</h2>

<h3>5.1. Пользователь имеет право:</h3>
<ul>
<li>Создавать, редактировать и удалять встречи.</li>
<li>Присоединяться к встречам, созданным другими Пользователями, путём отправки заявки на участие.</li>
<li>Общаться с другими Пользователями через чат в Приложении после совместного участия во встрече или одобрения заявки.</li>
<li>Загружать и управлять фотографиями профиля (с учётом модерации).</li>
<li>Оценивать других Пользователей и заведения после встреч.</li>
<li>Сообщать о нарушениях другими Пользователями.</li>
<li>Запрашивать удаление своего аккаунта и персональных данных.</li>
</ul>

<h3>5.2. Пользователь обязан:</h3>
<ul>
<li>Соблюдать условия настоящего Соглашения и действующее законодательство Республики Казахстан.</li>
<li>Предоставлять достоверную и правдивую информацию в своём профиле.</li>
<li>Относиться к другим Пользователям с уважением, воздерживаться от притеснений, дискриминации, угроз и оскорблений.</li>
<li>Не публиковать незаконный, оскорбительный, непристойный или вредоносный контент.</li>
<li>Не использовать Приложение в коммерческих целях, для рекламы или спама без согласия Исполнителя.</li>
<li>Не пытаться получить несанкционированный доступ к системам Приложения, аккаунтам других Пользователей или серверной инфраструктуре.</li>
<li>Не использовать автоматизированные средства (боты, скрейперы) для взаимодействия с Приложением.</li>
</ul>

<h2>6. Права и обязанности Исполнителя</h2>

<h3>6.1. Исполнитель имеет право:</h3>
<ul>
<li>Изменять, обновлять или прекращать работу Приложения или любых его функций в любое время без предварительного уведомления.</li>
<li>Осуществлять модерацию Контента Пользователей, включая фотографии, информацию профиля и сообщения, и удалять любой Контент, нарушающий настоящее Соглашение.</li>
<li>Приостанавливать или окончательно блокировать учётную запись Пользователя за нарушения настоящего Соглашения.</li>
<li>Направлять Пользователям уведомления об обновлениях Приложения, изменениях политик и информации, связанной с сервисом.</li>
<li>Собирать и обрабатывать анонимизированные данные об использовании для аналитики и улучшения сервиса.</li>
</ul>

<h3>6.2. Исполнитель обязан:</h3>
<ul>
<li>Прилагать разумные усилия для обеспечения доступности и надлежащего функционирования Приложения.</li>
<li>Защищать персональные данные Пользователей в соответствии с Политикой конфиденциальности и применимым законодательством о защите данных.</li>
<li>Обрабатывать запросы на удаление аккаунтов в течение 30 дней.</li>
<li>Уведомлять Пользователей о существенных изменениях настоящего Соглашения.</li>
</ul>

<h2>7. Контент и интеллектуальная собственность</h2>
<p>7.1. Пользователь сохраняет право собственности на Контент, который он публикует в Приложении. Публикуя Контент, Пользователь предоставляет Исполнителю неисключительную, всемирную, безвозмездную лицензию на отображение, распространение и обработку такого Контента в рамках Приложения в целях предоставления услуг.</p>
<p>7.2. Приложение, его дизайн, исходный код, логотипы и все связанные материалы являются интеллектуальной собственностью Исполнителя и защищены применимым законодательством об интеллектуальной собственности.</p>
<p>7.3. Пользователь не вправе копировать, изменять, распространять или создавать производные работы на основе Приложения или любых его компонентов.</p>

<h2>8. Встречи и взаимодействие Пользователей</h2>
<p>8.1. Исполнитель является платформой, которая способствует установлению связей между Пользователями. Исполнитель не является стороной, организатором или участником каких-либо встреч. Исполнитель не выступает агентом, посредником или представителем какого-либо Пользователя.</p>
<p>8.2. Пользователи организуют и участвуют во встречах исключительно на свой собственный риск и под свою ответственность. Исполнитель не несёт никакой ответственности за любые действия, бездействие, поведение, высказывания или ущерб, возникающие в результате взаимодействия Пользователей до, во время или после встреч.</p>
<p>8.3. Исполнитель не проверяет личность, биографию, судимость или намерения Пользователей помимо информации, предоставленной при регистрации, и модерации фотографий. Исполнитель не даёт никаких заверений или гарантий относительно характера, надёжности, безопасности или законопослушности какого-либо Пользователя.</p>
<p>8.4. Исполнитель не несёт ответственности за любой физический, эмоциональный, психологический, финансовый или материальный вред, травму, ущерб или потерю, которые могут возникнуть в результате встреч, совместных мероприятий или любого другого взаимодействия между Пользователями, организованного через Приложение или иным образом.</p>
<p>8.5. Исполнитель не несёт ответственности за любые незаконные, противоправные, оскорбительные, угрожающие, клеветнические или иным образом вредоносные действия, совершённые Пользователями как во время встреч, так и посредством сообщений, контента профиля или любого другого использования Приложения.</p>
<p>8.6. Пользователи признают, что взаимодействие с другими Пользователями, включая личные встречи, сопряжено с неотъемлемыми рисками. Пользователи несут единоличную ответственность за принятие надлежащих мер безопасности при участии во встречах, включая, но не ограничиваясь, выбор общественных мест для встреч, информирование третьих лиц о своих планах и проявление личной осмотрительности.</p>
<p>8.7. Исполнитель не рекомендует, не поддерживает и не гарантирует какие-либо встречи, мероприятия, заведения или Пользователей. Любая информация, отображаемая в Приложении о заведениях, акциях или мероприятиях, предоставляется исключительно в информационных целях.</p>

<h2>9. Ответственность Пользователя и возмещение убытков</h2>
<p>9.1. Пользователь полностью и единолично несёт ответственность за свои действия, поведение и любые последствия, возникающие в связи с использованием Приложения и участием во встречах.</p>
<p>9.2. Пользователь обязуется соблюдать все применимые законы и нормативные акты Республики Казахстан при использовании Приложения и во время любых встреч или взаимодействий с другими Пользователями.</p>
<p>9.3. Пользователь обязуется не использовать Приложение в незаконных, мошеннических, злонамеренных или вредоносных целях, включая, но не ограничиваясь:</p>
<ul>
<li>Совершение или содействие совершению уголовных правонарушений.</li>
<li>Притеснение, преследование, запугивание или угрозы в отношении других Пользователей.</li>
<li>Распространение запрещённых веществ или участие в незаконной торговле.</li>
<li>Вымогательство денежных средств, товаров или услуг под ложным предлогом.</li>
<li>Организация мероприятий, нарушающих общественный порядок или нравственность.</li>
<li>Распространение экстремистского, разжигающего ненависть, дискриминационного или насильственного контента.</li>
<li>Выдача себя за другое лицо или искажение своей личности.</li>
<li>Сбор персональных данных других Пользователей в несанкционированных целях.</li>
</ul>
<p>9.4. Пользователь обязуется возместить, защитить и оградить Исполнителя, его владельцев, руководителей, сотрудников, агентов и аффилированных лиц от всех претензий, требований, исков, убытков, ущерба, обязательств, расходов и издержек (включая разумные расходы на юридическую помощь), возникающих из или связанных с:</p>
<ul>
<li>Использованием Приложения или участием во встречах Пользователем.</li>
<li>Нарушением Пользователем настоящего Соглашения или любого применимого закона.</li>
<li>Контентом или информацией, предоставленной Пользователем через Приложение.</li>
<li>Любым вредом, травмой или ущербом, причинённым Пользователем другим Пользователям или третьим лицам.</li>
<li>Любым спором между Пользователем и другими Пользователями.</li>
</ul>
<p>9.5. Пользователь признаёт, что Исполнитель не является страховщиком и не предоставляет какого-либо страхового покрытия для встреч или взаимодействий Пользователей. Пользователю рекомендуется самостоятельно оформить страхование, если он считает это необходимым.</p>
<p>9.6. В случае если Исполнитель понесёт какие-либо убытки, ущерб, штрафы или санкции в результате незаконных или неправомерных действий Пользователя, Пользователь обязан в полном объёме возместить Исполнителю все такие убытки.</p>

<h2>10. Ограничение ответственности</h2>
<p>10.1. Исполнитель не несёт ответственности за:</p>
<ul>
<li>Любые прямые, косвенные, случайные, особые или последующие убытки, возникающие в результате использования или невозможности использования Приложения.</li>
<li>Любой вред, травму, ущерб или потерю, возникающие в результате встреч, совместных мероприятий или взаимодействий между Пользователями.</li>
<li>Действия, поведение, высказывания или бездействие других Пользователей как в онлайн-, так и в офлайн-режиме, в том числе во время встреч.</li>
<li>Любые уголовные, противоправные или вредоносные действия, совершённые Пользователями.</li>
<li>Точность, полноту, правдивость или достоверность информации, предоставляемой Пользователями в профилях, сообщениях или описаниях встреч.</li>
<li>Качество, безопасность, законность или пригодность любого заведения, мероприятия или события, связанного со встречами.</li>
<li>Временную недоступность Приложения в связи с техническим обслуживанием, обновлениями или обстоятельствами непреодолимой силы.</li>
<li>Потерю данных Пользователя в результате обстоятельств, находящихся вне разумного контроля Исполнителя.</li>
<li>Любые последствия решения Пользователя встретиться с другими Пользователями лично.</li>
</ul>
<p>10.2. Исполнитель предоставляет Приложение исключительно как технологическую платформу для содействия социальным контактам. Исполнитель не контролирует и не может нести ответственность за поведение Пользователей за пределами Приложения.</p>
<p>10.3. В любом случае совокупная ответственность Исполнителя перед Пользователем не может превышать сумму, уплаченную Пользователем за платные услуги (при наличии таковых) за 12 месяцев, предшествующих предъявлению претензии.</p>
<p>10.4. Пользователь соглашается, что ограничения ответственности, установленные в настоящем Соглашении, являются разумными и составляют существенную основу соглашения между Пользователем и Исполнителем.</p>

<h2>11. Блокировка и удаление аккаунта</h2>
<p>11.1. Исполнитель вправе приостановить или удалить учётную запись Пользователя без предварительного уведомления, если Пользователь:</p>
<ul>
<li>Нарушает условия настоящего Соглашения.</li>
<li>Публикует запрещённый Контент.</li>
<li>Допускает притеснения, угрозы или оскорбительное поведение в отношении других Пользователей.</li>
<li>Использует Приложение в мошеннических или незаконных целях.</li>
<li>Создаёт множественные аккаунты.</li>
<li>Причиняет вред, травму или ущерб другим Пользователям или третьим лицам во время встреч или через Приложение.</li>
</ul>
<p>11.2. Заблокированный Пользователь может обжаловать решение, обратившись к Исполнителю по адресу <a href="mailto:administrator@tanys.app">administrator@tanys.app</a>.</p>
<p>11.3. Исполнитель оставляет за собой право сообщать о любой незаконной деятельности, обнаруженной через Приложение, соответствующим правоохранительным органам.</p>

<h2>12. Разрешение споров</h2>
<p>12.1. Все споры, возникающие из настоящего Соглашения, разрешаются путём переговоров. Если стороны не смогут достичь согласия, споры разрешаются в соответствии с законодательством Республики Казахстан.</p>
<p>12.2. Пользователь может направить жалобы или претензии на адрес <a href="mailto:administrator@tanys.app">administrator@tanys.app</a>. Исполнитель обязуется ответить в течение 15 рабочих дней.</p>
<p>12.3. Любые споры между Пользователями, возникающие в связи со встречами или взаимодействиями, разрешаются исключительно между вовлечёнными Пользователями. Исполнитель не является стороной таких споров и не несёт обязательств по их посредничеству или разрешению.</p>

<h2>13. Изменения в Соглашении</h2>
<p>13.1. Исполнитель оставляет за собой право изменять настоящее Соглашение в любое время. Изменения вступают в силу с момента публикации обновлённого Соглашения в Приложении.</p>
<p>13.2. Пользователь будет уведомлён о существенных изменениях посредством уведомления в Приложении или по электронной почте. Продолжение использования Приложения после вступления в силу изменений означает принятие обновлённых условий.</p>
<p>13.3. Если Пользователь не согласен с изменениями, он обязан прекратить использование Приложения и может запросить удаление своего аккаунта.</p>

<h2>14. Заключительные положения</h2>
<p>14.1. Настоящее Соглашение регулируется законодательством Республики Казахстан.</p>
<p>14.2. Если какое-либо положение настоящего Соглашения будет признано недействительным или не подлежащим исполнению, остальные положения сохраняют полную юридическую силу.</p>
<p>14.3. Настоящее Соглашение представляет собой полное соглашение между Пользователем и Исполнителем относительно использования Приложения.</p>
<p>14.4. Принятие настоящего Соглашения (путём регистрации в Приложении) равнозначно заключению письменного договора.</p>
<p>14.5. Принимая настоящее Соглашение, Пользователь подтверждает, что прочитал, понял и согласен соблюдать все положения, включая ограничения ответственности, обязательства по возмещению убытков и признание рисков, связанных со встречами с другими Пользователями.</p>

<h2>15. Контактная информация</h2>
<p>По вопросам, связанным с настоящим Соглашением, обращайтесь:</p>
<ul>
<li><strong>Компания</strong>: ТОО AMIR TRADE (Amir Trade LLP)</li>
<li><strong>Email</strong>: <a href="mailto:administrator@tanys.app">administrator@tanys.app</a></li>
</ul>
HTML;
    }

    private function offerKk(): string
    {
        return <<<'HTML'
<h2>1. Жалпы ережелер</h2>
<p>Осы жария оферта шарты (бұдан әрі — «Келісім») Tanys мобильді қосымшасы әкімшілігінің (бұдан әрі — «Орындаушы») кез келген жеке тұлғаға (бұдан әрі — «Пайдаланушы») Tanys мобильді қосымшасын (бұдан әрі — «Қосымша») төменде көрсетілген шарттарда пайдалану туралы шарт жасасуға арналған ресми ұсынысы болып табылады.</p>
<p>Қазақстан Республикасының заңнамасына сәйкес бұл құжат жария оферта болып табылады және төменде баяндалған шарттарды қабылдау (Қосымшада тіркелу) осы Келісімді қосылу шарттарында жасасуды білдіреді.</p>

<h2>2. Терминдер мен анықтамалар</h2>
<ul>
<li><strong>Қосымша</strong> — iOS және Android платформаларында қолжетімді, әлеуметтік кездесулерді ұйымдастыруға және табуға арналған Tanys мобильді қосымшасы.</li>
<li><strong>Пайдаланушы</strong> — 16 жасқа толған, Қосымшада тіркелген және осы Келісім шарттарын қабылдаған жеке тұлға.</li>
<li><strong>Кездесу</strong> — Пайдаланушы Қосымша арқылы белсенділік түрін, күнін, уақытын, орнын және сипаттамасын көрсете отырып жасайтын әлеуметтік іс-шара.</li>
<li><strong>Профиль</strong> — Пайдаланушының Қосымшадағы жеке беті, онда аты, жасы, жынысы, қаласы, фотосуреттері, сипаттамасы және басқа ақпарат бар.</li>
<li><strong>Мазмұн</strong> — Пайдаланушы Қосымша арқылы жариялайтын немесе жіберетін кез келген ақпарат, мәтіндер, фотосуреттер, хабарламалар және басқа материалдар.</li>
<li><strong>Модерация</strong> — Орындаушының Пайдаланушылар мазмұнын осы Келісімге және қолданыстағы заңнамаға сәйкестігін тексеру процесі.</li>
</ul>

<h2>3. Келісім мәні</h2>
<p>3.1. Орындаушы Пайдаланушыға Қосымшаны мақсаты бойынша пайдалануға ерекше емес, ауыстырылмайтын құқық береді: әлеуметтік кездесулер жасау және қосылу, басқа Пайдаланушылармен байланысу, орындарды табу және акцияларды қарау.</p>
<p>3.2. Қосымша «сол қалпында» негізінде ұсынылады. Орындаушы Қосымшаның үздіксіз немесе қатесіз жұмысына кепілдік бермейді.</p>
<p>3.3. Қосымшаны пайдалану тегін. Орындаушы болашақта Пайдаланушыларға алдын ала хабарлай отырып, ақылы функцияларды енгізу құқығын сақтайды.</p>

<h2>4. Тіркелу және тіркелгі</h2>
<p>4.1. Қосымшаны пайдалану үшін Пайдаланушы жарамды телефон нөмірін, атын, жасын, жынысын, электрондық пошта мекенжайын көрсетіп және қаланы таңдап тіркелуі керек.</p>
<p>4.2. Пайдаланушы тіркелу кезінде берілген барлық ақпараттың дұрыс және өзекті екенін растайды. Пайдаланушы бұл ақпаратты өзекті ұстауға жауапты.</p>
<p>4.3. Пайдаланушы тіркелгі деректерінің құпиялылығын сақтауға және тіркелгісі бойынша жасалған барлық әрекеттерге жеке жауапты.</p>
<p>4.4. Бір жеке тұлға Қосымшада тек бір тіркелгіге ие бола алады. Бірнеше тіркелгі жасау тіркелгінің бұғатталуына әкелуі мүмкін.</p>

<h2>5. Пайдаланушының құқықтары мен міндеттері</h2>

<h3>5.1. Пайдаланушы құқылы:</h3>
<ul>
<li>Кездесулер жасауға, өңдеуге және жоюға.</li>
<li>Басқа Пайдаланушылар жасаған кездесулерге қосылу сұранысын жіберу арқылы қосылуға.</li>
<li>Кездесуге бірлесіп қатысқаннан немесе қосылу сұранысы мақұлданғаннан кейін Қосымша ішіндегі чат арқылы басқа Пайдаланушылармен байланысуға.</li>
<li>Профиль фотосуреттерін жүктеуге және басқаруға (модерацияға сәйкес).</li>
<li>Кездесулерден кейін басқа Пайдаланушылар мен орындарды бағалауға.</li>
<li>Басқа Пайдаланушылардың бұзушылықтары туралы хабарлауға.</li>
<li>Тіркелгі мен жеке деректерін жоюды сұрауға.</li>
</ul>

<h3>5.2. Пайдаланушы міндетті:</h3>
<ul>
<li>Осы Келісім шарттарын және Қазақстан Республикасының қолданыстағы заңнамасын сақтауға.</li>
<li>Профилінде дұрыс және шынайы ақпарат беруге.</li>
<li>Басқа Пайдаланушыларға құрметпен қарауға, қысым жасаудан, кемсітуден, қорқытудан және қорлаудан бас тартуға.</li>
<li>Заңсыз, қорлайтын, ұятсыз немесе зиянды мазмұнды жарияламауға.</li>
<li>Қосымшаны коммерциялық мақсаттарда, жарнама немесе спам үшін Орындаушының келісімінсіз пайдаланбауға.</li>
<li>Қосымша жүйелеріне, басқа Пайдаланушылардың тіркелгілеріне немесе сервер инфрақұрылымына рұқсатсыз кіруге әрекеттенбеуге.</li>
<li>Қосымшамен өзара әрекеттесу үшін автоматтандырылған құралдарды (боттар, скрейперлер) пайдаланбауға.</li>
</ul>

<h2>6. Орындаушының құқықтары мен міндеттері</h2>

<h3>6.1. Орындаушы құқылы:</h3>
<ul>
<li>Қосымшаны немесе оның кез келген функциясын кез келген уақытта алдын ала ескертусіз өзгертуге, жаңартуға немесе тоқтатуға.</li>
<li>Пайдаланушылар мазмұнын, соның ішінде фотосуреттерді, профиль ақпаратын және хабарламаларды модерациялауға және осы Келісімді бұзатын кез келген мазмұнды жоюға.</li>
<li>Осы Келісімді бұзғаны үшін Пайдаланушының тіркелгісін тоқтата тұруға немесе түбегейлі бұғаттауға.</li>
<li>Пайдаланушыларға Қосымша жаңартулары, саясат өзгерістері және сервиске қатысты ақпарат туралы хабарландырулар жіберуге.</li>
<li>Аналитика және жақсарту мақсатында анонимделген пайдалану деректерін жинауға және өңдеуге.</li>
</ul>

<h3>6.2. Орындаушы міндетті:</h3>
<ul>
<li>Қосымшаның қолжетімділігі мен тиісті жұмысын қамтамасыз ету үшін ақылға қонымды күш-жігер жұмсауға.</li>
<li>Құпиялылық саясатына және деректерді қорғау туралы қолданыстағы заңнамаға сәйкес Пайдаланушылардың жеке деректерін қорғауға.</li>
<li>Тіркелгіні жою сұраныстарын 30 күн ішінде өңдеуге.</li>
<li>Пайдаланушыларды осы Келісімге елеулі өзгерістер туралы хабардар етуге.</li>
</ul>

<h2>7. Мазмұн және зияткерлік меншік</h2>
<p>7.1. Пайдаланушы Қосымшада жариялайтын мазмұнға меншік құқығын сақтайды. Мазмұнды жариялай отырып, Пайдаланушы Орындаушыға қызмет көрсету мақсатында осындай мазмұнды Қосымша шеңберінде көрсетуге, таратуға және өңдеуге ерекше емес, бүкіләлемдік, тегін лицензия береді.</p>
<p>7.2. Қосымша, оның дизайны, бастапқы коды, логотиптері және барлық байланысты материалдар Орындаушының зияткерлік меншігі болып табылады және қолданыстағы зияткерлік меншік туралы заңнамамен қорғалған.</p>
<p>7.3. Пайдаланушы Қосымшаны немесе оның кез келген компоненттерін көшіруге, өзгертуге, таратуға немесе туынды жұмыстар жасауға құқылы емес.</p>

<h2>8. Кездесулер және Пайдаланушылардың өзара әрекеттесуі</h2>
<p>8.1. Орындаушы Пайдаланушылар арасында байланыс орнатуға ықпал ететін платформа болып табылады. Орындаушы кез келген кездесудің тарапы, ұйымдастырушысы немесе қатысушысы болып табылмайды. Орындаушы кез келген Пайдаланушының агенті, делдалы немесе өкілі ретінде әрекет етпейді.</p>
<p>8.2. Пайдаланушылар кездесулерді толығымен өз тәуекелі мен жауапкершілігімен ұйымдастырады және оларға қатысады. Орындаушы кездесулерге дейін, кездесулер кезінде немесе одан кейін Пайдаланушылардың өзара әрекеттесуінен туындайтын кез келген әрекеттер, әрекетсіздіктер, мінез-құлық, пікірлер немесе залал үшін ешқандай жауапкершілік көтермейді.</p>
<p>8.3. Орындаушы тіркелу кезінде берілген ақпарат пен фотосуреттерді модерациялаудан басқа Пайдаланушылардың жеке басын, өмірбаянын, қылмыстық тарихын немесе ниеттерін тексермейді. Орындаушы кез келген Пайдаланушының мінезі, сенімділігі, қауіпсіздігі немесе заңға бағынушылығы туралы ешқандай мәлімдемелер немесе кепілдіктер бермейді.</p>
<p>8.4. Орындаушы кездесулер, бірлескен іс-шаралар немесе Пайдаланушылар арасындағы кез келген өзара әрекеттесу нәтижесінде туындауы мүмкін кез келген физикалық, эмоциялық, психологиялық, қаржылық немесе материалдық зиян, жарақат, залал немесе шығын үшін жауапкершілік көтермейді, олар Қосымша арқылы немесе өзгеше түрде ұйымдастырылған болсын.</p>
<p>8.5. Орындаушы Пайдаланушылар жасаған кез келген заңсыз, құқыққа қайшы, қорлайтын, қорқытатын, жала жабатын немесе өзгеше зиянды әрекеттер үшін жауапкершілік көтермейді, олар кездесулер кезінде немесе хабарламалар, профиль мазмұны немесе Қосымшаны кез келген басқа пайдалану арқылы жасалған болсын.</p>
<p>8.6. Пайдаланушылар басқа Пайдаланушылармен өзара әрекеттесудің, оның ішінде жеке кездесудің, тән тәуекелдері бар екенін мойындайды. Пайдаланушылар кездесулерге қатысу кезінде тиісті қауіпсіздік шараларын қабылдауға жеке жауапты, оның ішінде қоғамдық кездесу орындарын таңдау, үшінші тұлғаларды өз жоспарлары туралы хабардар ету және жеке ұқыптылық танытуды қоса алғанда, бірақ олармен шектелмей.</p>
<p>8.7. Орындаушы кез келген кездесулерді, іс-шараларды, мекемелерді немесе Пайдаланушыларды ұсынбайды, қолдамайды және кепілдік бермейді. Қосымшада орындар, акциялар немесе іс-шаралар туралы көрсетілген кез келген ақпарат тек ақпараттық мақсатта ұсынылады.</p>

<h2>9. Пайдаланушының жауапкершілігі және залалды өтеу</h2>
<p>9.1. Пайдаланушы Қосымшаны пайдаланудан және кездесулерге қатысудан туындайтын өз әрекеттері, мінез-құлқы және кез келген салдарлар үшін толық және жеке жауапты.</p>
<p>9.2. Пайдаланушы Қосымшаны пайдалану кезінде және басқа Пайдаланушылармен кез келген кездесулер немесе өзара әрекеттесулер кезінде Қазақстан Республикасының барлық қолданыстағы заңдары мен нормативтік актілерін сақтауға міндеттенеді.</p>
<p>9.3. Пайдаланушы Қосымшаны заңсыз, алаяқтық, зиянкестік немесе зиянды мақсаттарда пайдаланбауға міндеттенеді, оның ішінде, бірақ олармен шектелмей:</p>
<ul>
<li>Қылмыстық құқық бұзушылықтар жасау немесе оларға жәрдемдесу.</li>
<li>Басқа Пайдаланушыларға қатысты қысым жасау, қудалау, запугивание немесе қорқыту.</li>
<li>Тыйым салынған заттарды тарату немесе заңсыз саудаға қатысу.</li>
<li>Жалған сылтаумен ақша, тауарлар немесе қызметтер талап ету.</li>
<li>Қоғамдық тәртіпті немесе адамгершілікті бұзатын іс-шаралар ұйымдастыру.</li>
<li>Экстремистік, жеккөрушілікті қоздыратын, кемсітушілік немесе зорлық-зомбылық мазмұнды тарату.</li>
<li>Өзін басқа адам ретінде көрсету немесе жеке басын бұрмалау.</li>
<li>Басқа Пайдаланушылардың жеке деректерін рұқсатсыз мақсаттарда жинау.</li>
</ul>
<p>9.4. Пайдаланушы Орындаушыны, оның иелерін, басшыларын, қызметкерлерін, агенттерін және аффилиирленген тұлғаларын мынадан туындайтын немесе байланысты барлық талаптар, талап-арыздар, сот талаптары, шығындар, залалдар, міндеттемелер, шығыстар мен жұмсалымдардан (ақылға қонымды заңгерлік шығыстарды қоса алғанда) қорғауға, өтеуге және сақтандыруға міндеттенеді:</p>
<ul>
<li>Пайдаланушының Қосымшаны пайдалануы немесе кездесулерге қатысуы.</li>
<li>Пайдаланушының осы Келісімді немесе кез келген қолданыстағы заңды бұзуы.</li>
<li>Пайдаланушының Қосымша арқылы берген мазмұны немесе ақпараты.</li>
<li>Пайдаланушының басқа Пайдаланушыларға немесе үшінші тұлғаларға келтірген кез келген зияны, жарақаты немесе залалы.</li>
<li>Пайдаланушы мен басқа Пайдаланушылар арасындағы кез келген дау.</li>
</ul>
<p>9.5. Пайдаланушы Орындаушының сақтандырушы емес екенін және кездесулер немесе Пайдаланушылардың өзара әрекеттесуі үшін ешқандай сақтандыру қамтуын қамтамасыз етпейтінін мойындайды. Пайдаланушыға қажет деп санаған жағдайда өз бетінше сақтандыру рәсімдеуі ұсынылады.</p>
<p>9.6. Пайдаланушының заңсыз немесе құқыққа қайшы әрекеттері нәтижесінде Орындаушы кез келген шығындар, залалдар, айыппұлдар немесе санкциялар шеккен жағдайда, Пайдаланушы Орындаушыға барлық осындай шығындарды толық көлемде өтеуге міндетті.</p>

<h2>10. Жауапкершілікті шектеу</h2>
<p>10.1. Орындаушы мыналар үшін жауапкершілік көтермейді:</p>
<ul>
<li>Қосымшаны пайдаланудан немесе пайдалана алмаудан туындайтын кез келген тікелей, жанама, кездейсоқ, арнайы немесе салдарлы залалдар.</li>
<li>Кездесулер, бірлескен іс-шаралар немесе Пайдаланушылар арасындағы өзара әрекеттесулерден туындайтын кез келген зиян, жарақат, залал немесе шығын.</li>
<li>Басқа Пайдаланушылардың онлайн немесе офлайн режимдегі, соның ішінде кездесулер кезіндегі әрекеттері, мінез-құлқы, пікірлері немесе әрекетсіздіктері.</li>
<li>Пайдаланушылар жасаған кез келген қылмыстық, құқыққа қайшы немесе зиянды әрекеттер.</li>
<li>Пайдаланушылар профильдерінде, хабарламаларында немесе кездесулер сипаттамаларында берген ақпараттың дұрыстығы, толықтығы, шынайылығы немесе сенімділігі.</li>
<li>Кездесулерге байланысты кез келген мекеменің, іс-шараның немесе оқиғаның сапасы, қауіпсіздігі, заңдылығы немесе жарамдылығы.</li>
<li>Техникалық қызмет көрсету, жаңартулар немесе форс-мажорлық жағдайларға байланысты Қосымшаның уақытша қолжетімсіздігі.</li>
<li>Орындаушының ақылға қонымды бақылауынан тыс жағдайлар нәтижесінде Пайдаланушы деректерінің жоғалуы.</li>
<li>Пайдаланушының басқа Пайдаланушылармен жеке кездесу туралы шешімінің кез келген салдары.</li>
</ul>
<p>10.2. Орындаушы Қосымшаны тек әлеуметтік байланыстарға жәрдемдесуге арналған технологиялық платформа ретінде ұсынады. Орындаушы Пайдаланушылардың Қосымшадан тыс мінез-құлқын бақыламайды және ол үшін жауапты бола алмайды.</p>
<p>10.3. Кез келген жағдайда Орындаушының Пайдаланушы алдындағы жиынтық жауапкершілігі Пайдаланушы претензия қою алдындағы 12 ай ішінде ақылы қызметтер үшін (бар болса) төлеген сомадан аспайды.</p>
<p>10.4. Пайдаланушы осы Келісімде белгіленген жауапкершілікті шектеулердің ақылға қонымды екенін және Пайдаланушы мен Орындаушы арасындағы келісімнің маңызды негізін құрайтынын мойындайды.</p>

<h2>11. Бұғаттау және тіркелгіні жою</h2>
<p>11.1. Орындаушы Пайдаланушының тіркелгісін алдын ала ескертусіз тоқтата тұруға немесе жоюға құқылы, егер Пайдаланушы:</p>
<ul>
<li>Осы Келісім шарттарын бұзса.</li>
<li>Тыйым салынған мазмұнды жарияласа.</li>
<li>Басқа Пайдаланушыларға қатысты қысым жасауға, қорқытуға немесе қорлауға жол берсе.</li>
<li>Қосымшаны алаяқтық немесе заңсыз мақсаттарда пайдаланса.</li>
<li>Бірнеше тіркелгі жасаса.</li>
<li>Кездесулер кезінде немесе Қосымша арқылы басқа Пайдаланушыларға немесе үшінші тұлғаларға зиян, жарақат немесе залал келтірсе.</li>
</ul>
<p>11.2. Бұғатталған Пайдаланушы шешімге <a href="mailto:administrator@tanys.app">administrator@tanys.app</a> мекенжайына хабарласу арқылы шағымдана алады.</p>
<p>11.3. Орындаушы Қосымша арқылы анықталған кез келген заңсыз әрекет туралы тиісті құқық қорғау органдарына хабарлау құқығын сақтайды.</p>

<h2>12. Дауларды шешу</h2>
<p>12.1. Осы Келісімнен туындайтын барлық даулар келіссөздер арқылы шешіледі. Тараптар келісімге қол жеткізе алмаған жағдайда, даулар Қазақстан Республикасының заңнамасына сәйкес шешіледі.</p>
<p>12.2. Пайдаланушы шағымдар мен талаптарды <a href="mailto:administrator@tanys.app">administrator@tanys.app</a> мекенжайына жібере алады. Орындаушы 15 жұмыс күні ішінде жауап беруге міндеттенеді.</p>
<p>12.3. Кездесулер немесе өзара әрекеттесулерге байланысты Пайдаланушылар арасындағы кез келген даулар тек тартылған Пайдаланушылар арасында шешіледі. Орындаушы мұндай даулардың тарапы болып табылмайды және оларды делдалдау немесе шешу бойынша міндеттеме көтермейді.</p>

<h2>13. Келісімге өзгерістер</h2>
<p>13.1. Орындаушы осы Келісімді кез келген уақытта өзгерту құқығын сақтайды. Өзгерістер Қосымшада жаңартылған Келісімді жариялаған сәттен бастап күшіне енеді.</p>
<p>13.2. Пайдаланушыға елеулі өзгерістер туралы Қосымшадағы хабарландыру немесе электрондық пошта арқылы хабарланады. Өзгерістер күшіне енгеннен кейін Қосымшаны пайдалануды жалғастыру жаңартылған шарттарды қабылдауды білдіреді.</p>
<p>13.3. Пайдаланушы өзгерістермен келіспесе, Қосымшаны пайдалануды тоқтатуға және тіркелгісін жоюды сұрауға міндетті.</p>

<h2>14. Қорытынды ережелер</h2>
<p>14.1. Осы Келісім Қазақстан Республикасының заңнамасымен реттеледі.</p>
<p>14.2. Осы Келісімнің кез келген ережесі жарамсыз немесе орындалмайтын деп танылған жағдайда, қалған ережелер толық заңды күшінде қалады.</p>
<p>14.3. Осы Келісім Қосымшаны пайдалануға қатысты Пайдаланушы мен Орындаушы арасындағы толық келісім болып табылады.</p>
<p>14.4. Осы Келісімді қабылдау (Қосымшада тіркелу арқылы) жазбаша шарт жасасуға тең.</p>
<p>14.5. Осы Келісімді қабылдай отырып, Пайдаланушы барлық ережелерді, соның ішінде жауапкершілікті шектеулерді, залалды өтеу міндеттемелерін және басқа Пайдаланушылармен кездесуге байланысты тәуекелдерді мойындауды оқығанын, түсінгенін және сақтауға келісетінін растайды.</p>

<h2>15. Байланыс ақпараты</h2>
<p>Осы Келісімге қатысты сұрақтар бойынша хабарласыңыз:</p>
<ul>
<li><strong>Компания</strong>: AMIR TRADE ЖШС (Amir Trade LLP)</li>
<li><strong>Email</strong>: <a href="mailto:administrator@tanys.app">administrator@tanys.app</a></li>
</ul>
HTML;
    }
}
