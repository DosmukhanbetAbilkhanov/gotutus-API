<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\LegalPage;
use Illuminate\Database\Seeder;

class PrivacyPolicySeeder extends Seeder
{
    public function run(): void
    {
        // Skip if a privacy policy page already exists
        if (LegalPage::where('slug', LegalPage::SLUG_PRIVACY_POLICY)->exists()) {
            return;
        }

        $page = LegalPage::create([
            'slug' => LegalPage::SLUG_PRIVACY_POLICY,
            'version' => '1.0',
            'is_active' => true,
            'published_at' => now(),
        ]);

        $page->translations()->createMany([
            [
                'language_code' => 'en',
                'title' => 'Privacy Policy',
                'content' => $this->englishContent(),
            ],
            [
                'language_code' => 'ru',
                'title' => 'Политика конфиденциальности',
                'content' => $this->russianContent(),
            ],
            [
                'language_code' => 'kk',
                'title' => 'Құпиялылық саясаты',
                'content' => $this->kazakhContent(),
            ],
        ]);
    }

    private function englishContent(): string
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
<li>Email: <a href="mailto:administrator@tanys.app">administrator@tanys.app</a></li>
</ul>
HTML;
    }

    private function russianContent(): string
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
<li>Email: <a href="mailto:administrator@tanys.app">administrator@tanys.app</a></li>
</ul>
HTML;
    }

    private function kazakhContent(): string
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
<li>Email: <a href="mailto:administrator@tanys.app">administrator@tanys.app</a></li>
</ul>
HTML;
    }
}
