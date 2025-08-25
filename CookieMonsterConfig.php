<?php

namespace ProcessWire;

class CookieMonsterConfig extends Wire
{
    protected $data;

    protected $defaults = array(
        'titletext' => 'Diese Website verwendet Cookies',
        'bodytext' => "Wir verwenden Cookies, um Ihnen ein optimales Webseiten-Erlebnis zu bieten. Dazu zählen Cookies, die für den Betrieb der Seite und für die Steuerung unserer kommerziellen Unternehmensziele notwendig sind, sowie solche, die lediglich zu anonymen Statistikzwecken genutzt werden. Sie können selbst entscheiden, welche Kategorien Sie zulassen möchten. Bitte beachten Sie, dass auf Basis Ihrer Einstellungen womöglich nicht mehr alle Funktionalitäten der Seite zur Verfügung stehen. Weitere Informationen finden Sie in unseren Datenschutzhinweisen.",
        'buttontext_confirm' => "Auswahl bestätigen",
        'buttontext_accept' => "Alle auswählen",
        'use_stylesheet' => 1,
        'is_active' => 0,
        'multilanguage' => 0,
        'autolink' => 0,
        'target_string' => '',
        'target_page' => null,
        'imprint_page' => null,
        'cookies_necessary' => "wire|my-domain.de|Der Cookie ist für die sichere Anmeldung und die Erkennung von Spam oder Missbrauch der Webseite erforderlich.|Session\ncmnstr|my-domain.de|Speichert den Zustimmungsstatus des Benutzers für Cookies.|1 Jahr",
        'cookies_statistics' => "_ga|Google|Registriert eine eindeutige ID, die verwendet wird, um statistische Daten dazu, wie der Besucher die Website nutzt, zu generieren.|2 Jahre\n_gat|Google|Wird von Google Analytics verwendet, um die Anforderungsrate einzuschränken.|1 Tag\n_gid|Google|Registriert eine eindeutige ID, die verwendet wird, um statistische Daten dazu, wie der Besucher die Website nutzt, zu generieren|1 Tag",
        'cookies_ads' => '',
        'introtext_necessary' => 'Notwendige Cookies helfen dabei, eine Webseite nutzbar zu machen, indem sie Grundfunktionen wie Seitennavigation und Zugriff auf sichere Bereiche der Webseite ermöglichen. Die Webseite kann ohne diese Cookies nicht richtig funktionieren.',
        'introtext_statistics' => 'Statistik-Cookies helfen Webseiten-Besitzern zu verstehen, wie Besucher mit Webseiten interagieren, indem Informationen anonym gesammelt und gemeldet werden.',
        'introtext_ads' => '',
        'ga_property_id' => '',
        'table_placeholder' => '[[cookie-table]]',
        "referrer_policy_header" => "strict-origin-when-cross-origin",
        "strict_transport_security_header" => 31536000,
        "x_content_type_options_header" => 0,
        'conversion_count' => 2,
        'google_ads_conversions' => []
    );

    public function __construct(array $data)
    {
        $this->wire('config')->scripts->add(wire('config')->urls->CookieMonster.'CookieMonsterConfig.js');
        $this->wire('config')->styles->add(wire('config')->urls->CookieMonster.'CookieMonster.css');
        $this->wire('config')->scripts->add('https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js');
        $this->wire('config')->styles->add('https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');

        foreach ($this->defaults as $key => $value) {
            if (!isset($data[$key])) {
                $data[$key] = $value;
            }
        }

        if (!empty($data['google_ads_conversions'])) {
            if (is_string($data['google_ads_conversions'])) {
                $decoded = json_decode($data['google_ads_conversions'],true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $data['google_ads_conversions'] = $decoded;
                } else {
                    $data['google_ads_conversions'] = [];
                }
            } elseif (!is_array($data['google_ads_conversions'])) {
                $data['google_ads_conversions'] = [];
            }
        } else {
            $data['google_ads_conversions'] = [];
        }

        $this->data = $data;
    }

    public function getConfig()
    {
        $modules = wire('modules');
        $modules->get("JqueryWireTabs");

        $fields = new InputfieldWrapper();

        $tab = new InputfieldWrapper();
        $tab->attr("title", "Cookie-Banner");
        $tab->attr("class", "WireTab");

        $fieldset = $modules->get('InputfieldFieldset');
        $fieldset->label = __('Einstellungen');
        $fieldset->icon = 'cogs';
        $tab->add($fieldset);

        $field = $modules->get('InputfieldCheckbox');
        $field->label = __('Cookie-Banner aktiviert');
        $field->notes = __('Aktiviert die Anzeige des Cookie-Banners auf allen Seiten.');
        $field->name = 'is_active';
        $field->attr('value', 1);
        if($this->data['is_active']) {
            $field->attr('checked', 'checked');
        }
        $field->columnWidth = '34';
        $fieldset->append($field);

        $field = $modules->get('InputfieldCheckbox');
        $field->label = __('CookieMonster-Stylesheet verwenden');
        $field->notes = __('Stellt eine einfache Basis-Formatierung zur Verfügung.');
        $field->name = 'use_stylesheet';
        $field->attr('value', 1);
        if($this->data['use_stylesheet']) {
            $field->attr('checked', 'checked');
        }
        $field->columnWidth = '33';
        $fieldset->append($field);

        $field = $modules->get('InputfieldCheckbox');
        $field->label = __('Mehrsprachigkeit verwenden');
        $field->notes = __('Aktiviert mehrsprachig pflegbare Texte.');
        $field->name = 'multilanguage';
        $field->attr('value', $this->data['multilanguage']);
        if($this->data['multilanguage'] == 1) $field->attr('checked', 'checked');
        $field->columnWidth = '33';
        $fieldset->append($field);

        $field = $modules->get('InputfieldText');
        $field->label = __('Platzhalter für Cookie-Übersicht');
        $field->description = __('Dieser Platzhalter wird beim Seitenaufruf durch die Cookie-Übersicht ersetzt. So kann die die Cookie-Übersicht z.B. in der Datenschutzerkärung ausgegeben werden.');
        $field->notes = __('Voraussetzung: der TextformatterCookieTable muss installiert und in der entsprechenden Feldkonfiguration ausgewählt sein.');
        $field->attr('name', 'table_placeholder');
        $field->attr('value', $this->data['table_placeholder']);
        $field->columnWidth = '100';
        $fieldset->append($field);

        $fieldset = $modules->get('InputfieldFieldset');
        $fieldset->label = __('Banner-Text');
        $fieldset->icon = 'align-left';
        $fieldset->collapsed = Inputfield::collapsedYes;
        $tab->add($fieldset);

        $field = $modules->get('InputfieldText');
        $field->label = __('Banner-Überschrift');
        $field->attr('name', 'titletext');
        $field->attr('value', $this->data['titletext']);
        $field->columnWidth = '100';
        if($this->data['multilanguage'] == 1) $field->useLanguages = true;
        $fieldset->append($field);

        $field = $modules->get('InputfieldTextarea');
        $field->label = __('Banner-Text');
        $field->description = __('Der Text des Cookie-Banners');
        $field->notes = __('HTML ist erlaubt, Zeilenumbrüche werden automatisch in <br>-Elemente umgewandelt.');
        $field->attr('name', 'bodytext');
        $field->attr('value', $this->data['bodytext']);
        $field->columnWidth = '70';
        if($this->data['multilanguage'] == 1) $field->useLanguages = true;
        $fieldset->append($field);

        $field = $modules->get('InputfieldCheckbox');
        $field->label = __('Begriff automatisch verlinken');
        $field->notes = __('Aktiviert die automatische Verlinkung eines Begriffs (z.B. „Datenschutz") mit einer frei wählbaren Seite.');
        $field->name = 'autolink';
        $field->attr('value', 1);
        if($this->data['autolink']) {
            $field->attr('checked', 'checked');
        }
        $field->columnWidth = '30';
        $fieldset->append($field);

        $field = $modules->get('InputfieldText');
        $field->label = __('Begriff');
        $field->description = __('Begriff, der automatisch verlinkt werden soll');
        $field->attr('name', 'target_string');
        $field->attr('value', $this->data['target_string']);
        $field->columnWidth = '50';
        $field->showIf = 'autolink=1';
        if($this->data['multilanguage'] == 1) $field->useLanguages = true;
        $fieldset->append($field);

        $field = $modules->get('InputfieldPageListSelect');
        $field->label = __('Zielseite');
        $field->description = __('Seite, die automatisch mit dem Begriff verlinkt werden soll');
        $field->attr('name', 'target_page');
        $field->attr('value', $this->data['target_page']);
        $field->columnWidth = '50';
        $field->showIf = 'autolink=1';
        $fieldset->append($field);

        $field = $modules->get('InputfieldPageListSelect');
        $field->label = __('Impressum-Seite');
        $field->attr('name', 'imprint_page');
        $field->attr('value', $this->data['imprint_page']);
        $field->columnWidth = '100';
        $fieldset->append($field);

        $fieldset = $modules->get('InputfieldFieldset');
        $fieldset->label = __('Buttons');
        $fieldset->icon = 'map-signs';
        $fieldset->collapsed = Inputfield::collapsedYes;
        $tab->add($fieldset);

        $field = $modules->get('InputfieldText');
        $field->label = __('Beschriftung Auswahl-Button');
        $field->description = __('Beschriftung des Buttons, der mit gewählter Einstellung fortfährt');
        $field->attr('name', 'buttontext_confirm');
        $field->attr('value', $this->data['buttontext_confirm']);
        $field->columnWidth = '50';
        if($this->data['multilanguage'] == 1) $field->useLanguages = true;
        $fieldset->append($field);

        $field = $modules->get('InputfieldText');
        $field->label = __('Beschriftung Auswahl-Button');
        $field->description = __('Beschriftung des Buttons, der alle Cookies akzeptiert');
        $field->attr('name', 'buttontext_accept');
        $field->attr('value', $this->data['buttontext_accept']);
        $field->columnWidth = '50';
        if($this->data['multilanguage'] == 1) $field->useLanguages = true;
        $fieldset->append($field);

        $fieldset = $modules->get('InputfieldFieldset');
        $fieldset->label = __('Cookies');
        $fieldset->icon = 'certificate';
        $tab->add($fieldset);

        $necessary = $modules->get('InputfieldFieldset');
        $necessary->label = __('Notwendig');
        $necessary->icon = 'fire';
        $fieldset->add($necessary);

        $field = $modules->get('InputfieldTextarea');
        $field->label = __('Kurzbeschreibung für notwendige Cookies');
        $field->attr('name', 'introtext_necessary');
        $field->attr('value', $this->data['introtext_necessary']);
        $field->columnWidth = '100';
        if($this->data['multilanguage'] == 1) $field->useLanguages = true;
        $necessary->append($field);

        $field = $modules->get('InputfieldTextarea');
        $field->label = __('Notwendige Cookies');
        $field->description = __('Geben Sie hier Informationen zu den notwendigen Cookies in folgendem Format ein');
        $field->notes = __('Folgendes Format verwenden: Name|Anbieter|Zweck|Ablauf');
        $field->attr('name', 'cookies_necessary');
        $field->attr('value', $this->data['cookies_necessary']);
        $field->columnWidth = '100';
        if($this->data['multilanguage'] == 1) $field->useLanguages = true;
        $necessary->append($field);

        $statistics = $modules->get('InputfieldFieldset');
        $statistics->label = __('Statistik');
        $statistics->icon = 'signal';
        $fieldset->add($statistics);

        $field = $modules->get('InputfieldTextarea');
        $field->label = __('Kurzbeschreibung für Statistik Cookies');
        $field->attr('name', 'introtext_statistics');
        $field->attr('value', $this->data['introtext_statistics']);
        $field->columnWidth = '100';
        if($this->data['multilanguage'] == 1) $field->useLanguages = true;
        $statistics->append($field);

        $field = $modules->get('InputfieldTextarea');
        $field->label = __('Statistik Cookies');
        $field->description = __('Geben Sie hier Informationen zu den notwendigen Cookies in folgendem Format ein');
        $field->notes = __('Folgendes Format verwenden: Name|Anbieter|Zweck|Ablauf');
        $field->attr('name', 'cookies_statistics');
        $field->attr('value', $this->data['cookies_statistics']);
        $field->columnWidth = '100';
        if($this->data['multilanguage'] == 1) $field->useLanguages = true;
        $statistics->append($field);

        $ads = $modules->get('InputfieldFieldset');
        $ads->label = __('Marketing / Werbung');
        $ads->icon = 'bullhorn';
        $fieldset->add($ads);

        $field = $modules->get('InputfieldTextarea');
        $field->label = __('Kurzbeschreibung für Marketing Cookies');
        $field->attr('name', 'introtext_ads');
        $field->attr('value', $this->data['introtext_ads']);
        $field->columnWidth = '100';
        if($this->data['multilanguage'] == 1) $field->useLanguages = true;
        $ads->append($field);

        $field = $modules->get('InputfieldTextarea');
        $field->label = __('Marketing Cookies');
        $field->description = __('Geben Sie hier Informationen zu den Marketing-Cookies in folgendem Format ein');
        $field->notes = __('Folgendes Format verwenden: Name|Anbieter|Zweck|Ablauf');
        $field->attr('name', 'cookies_ads');
        $field->attr('value', $this->data['cookies_ads']);
        $field->columnWidth = '100';
        if($this->data['multilanguage'] == 1) $field->useLanguages = true;
        $ads->append($field);

        $fields->add($tab);

        $tab = new InputfieldWrapper();
        $tab->attr("title", "Tracking");
        $tab->attr("class", "WireTab");

        $fieldset = $modules->get('InputfieldFieldset');
        $fieldset->label = __('Google Analytics');
        $fieldset->icon = 'google';
        $tab->add($fieldset);

        $field = $modules->get('InputfieldText');
        $field->label = __('Property ID');
        $field->description = __('Tragen Sie hier eine Property-ID ein, um das Tracking zu aktivieren.');
        $field->notes = __('Erlaubte Formate:  
        Universal Analytics (UA-XXXXXXXX)  
        Google Analytics 4 (G-XXXXXXXX)  
        Google Ads (AW-XXXXXXXX)  
        Floodlight (DC-XXXXXXXX)
        Google Tag Manager Container (GTM-XXXXXXX)');
        $field->attr('name', 'ga_property_id');
        $field->attr('value', $this->data['ga_property_id']);
        $field->columnWidth = '100';

        $fieldset->add($field);
        $fields->add($tab);

        $tab = new InputfieldWrapper();
        $tab->attr("title", "Header");
        $tab->attr("class", "WireTab");

        $field = $modules->get("InputfieldRadios");
        $field->label = "Referrer-Policy-Header";
        $field->description = "Der Referrer-Policy-HTTP-Header steuert, wie viele Referrer-Informationen (die mit dem Referer-Header gesendet werden) in Anfragen enthalten sein sollen.";
        $field->notes = "Nähere Informationen: [Referrer-Policy auf MDN](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Referrer-Policy)";
        $field->addOptions([
            "no-referrer" => "no-referrer",
            "no-referrer-when-downgrade" => "no-referrer-when-downgrade",
            "origin" => "origin",
            "origin-when-cross-origin" => "origin-when-cross-origin",
            "same-origin" => "same-origin *(Empfehlung)*",
            "strict-origin" => "strict-origin",
            "strict-origin-when-cross-origin" => "strict-origin-when-cross-origin *(Standard)*",
            "unsafe-url" => "unsafe-url"
        ]);
        $field->attr("name+id", "referrer_policy_header");
        $field->attr("value", $this->data["referrer_policy_header"]);
        $tab->add($field);

        $field = $modules->get("InputfieldInteger");
        $field->label = "Strict Transport Security: max-age";
        $field->description = "Der HTTP Strict-Transport-Security-Antwortheader (oft als HSTS abgekürzt) informiert Browser darüber, dass auf die Website nur über HTTPS zugegriffen werden sollte und dass alle zukünftigen Zugriffsversuche über HTTP automatisch in HTTPS konvertiert werden sollten.";
        $field->notes = "Nähere Informationen: [Strict-Transport-Security auf MDN](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Strict-Transport-Security)";
        $field->attr("name+id", "strict_transport_security_header");
        $field->attr("value", $this->data["strict_transport_security_header"]);
        $tab->add($field);

        $field = $modules->get("InputfieldCheckbox");
        $field->label = "X-Content-Type-Options";
        $field->checkboxLabel = "Header auf `nosniff` setzen";
        $field->description = "Der HTTP Response header X-Content-Type-Options ist eine Markierung, die vom Server verwendet wird, um anzuzeigen, dass die in den Content-Type-Headern angekündigten MIME-Typen befolgt und nicht geändert werden sollten. Der Header ermöglicht es Ihnen, MIME-Typ-Sniffing zu vermeiden, indem Sie sagen, dass die MIME-Typen absichtlich konfiguriert sind. Website-Sicherheitstester erwarten normalerweise, dass dieser Header gesetzt wird.";
        $field->notes = "Nähere Informationen: [X-Content-Type-Options auf MDN](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Content-Type-Options)";
        $field->name = "x_content_type_options_header";
        $field->attr('value', 1);
        if($this->data['x_content_type_options_header']) {
            $field->attr('checked', 'checked');
        }
        $tab->add($field);

        $fields->add($tab);

        $tab = new InputfieldWrapper();
        $tab->attr("title", "Google Ads Conversions");
        $tab->attr("class", "WireTab");

        $count = $modules->get('InputfieldInteger');
        $count->name = 'conversion_count';
        $count->label = 'Anzahl der Conversion-Paare';
        $count->description = 'Legen Sie fest, wie viele Conversion-Paare Sie konfigurieren möchten.';
        $count->attr('value', $this->data['conversion_count'] ?? 2);
        $count->min = 1;
        $count->max = 50;
        $tab->add($count);

        $maxConversions = $this->data['conversion_count'] ?? 2;
        $conversions = $this->data['google_ads_conversions'] ?? [];

        for ($index = 0; $index < $maxConversions; $index++) {
            $conv = $conversions[$index] ?? [];

            $fieldset = $modules->get('InputfieldFieldset');
            $fieldset->label = "Conversion #" . ($index + 1);
            $fieldset->icon = 'google';
            $fieldset->collapsed = Inputfield::collapsedNo;

            $f = $modules->get('InputfieldText');
            $f->name = "google_ads_conversions[$index][label]";
            $f->label = "Bezeichnung";
            $f->value = $conv['label'] ?? '';
            $f->columnWidth = 50;
            $fieldset->add($f);

            $f = $modules->get('InputfieldText');
            $f->name = "google_ads_conversions[$index][id]";
            $f->label = "Google Ads ID";
            $f->value = $conv['id'] ?? '';
            $f->columnWidth = 50;
            $fieldset->add($f);

            $f = $modules->get('InputfieldText');
            $f->name = "google_ads_conversions[$index][description]";
            $f->label = "Beschreibung";
            $f->value = $conv['description'] ?? '';
            $fieldset->add($f);

            $f = $modules->get('InputfieldCheckbox');
            $f->name = "google_ads_conversions[$index][active]";
            $f->label = "Aktiv";
            $f->value = 1;
            $f->checked = !empty($conv['active']);
            $f->columnWidth = 100;
            $fieldset->add($f);

            $triggerFieldset = $modules->get('InputfieldFieldset');
            $triggerFieldset->label = "Auslösemethoden";
            $triggerFieldset->description = "Wählen Sie, wann diese Conversion ausgelöst werden soll";
            $triggerFieldset->icon = 'play';

            $pageCheckbox = $modules->get('InputfieldCheckbox');
            $pageCheckbox->name = "trigger_on_page_$index";
            $pageCheckbox->label = "✓ Auslösen bei Seitenaufruf";
            $pageCheckbox->description = "Conversion auslösen, wenn bestimmte Seiten geladen werden";
            $pageCheckbox->value = 1;
            $pageCheckbox->checked = !empty($conv['trigger_on_page']);
            $pageCheckbox->columnWidth = 50;
            $triggerFieldset->add($pageCheckbox);

            $formCheckbox = $modules->get('InputfieldCheckbox');
            $formCheckbox->name = "trigger_on_form_$index";
            $formCheckbox->label = "✓ Auslösen bei erfolgreicher Formularübermittlung";
            $formCheckbox->description = "Conversion auslösen, wenn Formulare erfolgreich gesendet werden";
            $formCheckbox->value = 1;
            $formCheckbox->checked = !empty($conv['trigger_on_form']);
            $formCheckbox->columnWidth = 50;
            $triggerFieldset->add($formCheckbox);

            $f = $modules->get('InputfieldPageListSelectMultiple');
            $f->name = "google_ads_conversions_pages_$index";
            $f->label = "Seiten (für Seitenaufruf-Trigger)";
            $f->description = "Seiten auswählen – wird nur verwendet, wenn 'Auslösen bei Seitenaufruf' oben aktiviert ist";
            $f->value = $conv['pages'] ?? [];
            $f->findPagesSelector = "template!=admin";
            $f->labelFieldName = "title";
            $triggerFieldset->add($f);

            $availableForms = [
                'anmeldung' => 'Anmeldung',
                'anmeldung-lg' => 'Anmeldung LG',
                'anfrage-firmenangebote' => 'Anfrage Firmenangebote',
                'kontaktformular' => 'Kontaktformular'
            ];

            $f = $modules->get('InputfieldCheckboxes');
            $f->name = "google_ads_conversions_forms_$index";
            $f->label = "Formulare (für Formular-Trigger)";
            $f->description = "Formulare auswählen – wird nur verwendet, wenn 'Auslösen bei erfolgreicher Formularübermittlung' oben aktiviert ist";
            $f->addOptions($availableForms);
            $f->value = $conv['form_names'] ?? [];
            $triggerFieldset->add($f);

            $fieldset->add($triggerFieldset);
            $tab->add($fieldset);
        }

        $fields->add($tab);

        return $fields;
    }
}