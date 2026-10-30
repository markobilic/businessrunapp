<?php

namespace App\PL\Production;

class PlService extends \SoapClient
{
    /**
     * @var array The defined classes
     */
    private static $classmap = [
        'PreuzmiPodatkeOPrivrednomSubjektu' => 'App\\PL\\Production\\PreuzmiPodatkeOPrivrednomSubjektu',
        'PrivredniSubjektiUlazniPodaci' => 'App\\PL\\Production\\PrivredniSubjektiUlazniPodaci',
        'PrivredniSubjekatMaticniBroj' => 'App\\PL\\Production\\PrivredniSubjekatMaticniBroj',
        'PrivredniSubjekatGrupaPodataka' => 'App\\PL\\Production\\PrivredniSubjekatGrupaPodataka',
        'PreuzmiPodatkeOPrivrednomSubjektuResponse' => 'App\\PL\\Production\\PreuzmiPodatkeOPrivrednomSubjektuResponse',
        'ArrayOfPrivredniSubjekat' => 'App\\PL\\Production\\ArrayOfPrivredniSubjekat',
        'PrivredniSubjekat' => 'App\\PL\\Production\\PrivredniSubjekat',
        'Grupa' => 'App\\PL\\Production\\Grupa',
        'Podatak' => 'App\\PL\\Production\\Podatak',
        'PreuzmiPromenePodatakaOPrivrednimSubjektima' => 'App\\PL\\Production\\PreuzmiPromenePodatakaOPrivrednimSubjektima',
        'PrivredniSubjektiPromenePoDatumu' => 'App\\PL\\Production\\PrivredniSubjektiPromenePoDatumu',
        'PreuzmiPromenePodatakaOPrivrednimSubjektimaResponse' => 'App\\PL\\Production\\PreuzmiPromenePodatakaOPrivrednimSubjektimaResponse',
        'SviPodaciODelatnostima' => 'App\\PL\\Production\\SviPodaciODelatnostima',
        'SviPodaciODelatnostimaResponse' => 'App\\PL\\Production\\SviPodaciODelatnostimaResponse',
        'Sifarnik' => 'App\\PL\\Production\\Sifarnik',
        'Zapis' => 'App\\PL\\Production\\Zapis',
        'SviPodaciODrzavama' => 'App\\PL\\Production\\SviPodaciODrzavama',
        'SviPodaciODrzavamaResponse' => 'App\\PL\\Production\\SviPodaciODrzavamaResponse',
        'SviPodaciOFunkcijamaOsnivacaZaduzbineIliFondacije' => 'App\\PL\\Production\\SviPodaciOFunkcijamaOsnivacaZaduzbineIliFondacije',
        'SviPodaciOFunkcijamaOsnivacaZaduzbineIliFondacijeResponse' => 'App\\PL\\Production\\SviPodaciOFunkcijamaOsnivacaZaduzbineIliFondacijeResponse',
        'SviPodaciOFunkcijamaZakonskogZastupnikaPrivrednogDrustva' => 'App\\PL\\Production\\SviPodaciOFunkcijamaZakonskogZastupnikaPrivrednogDrustva',
        'SviPodaciOFunkcijamaZakonskogZastupnikaPrivrednogDrustvaResponse' => 'App\\PL\\Production\\SviPodaciOFunkcijamaZakonskogZastupnikaPrivrednogDrustvaResponse',
        'SviPodaciOGrupamaDelatnosti' => 'App\\PL\\Production\\SviPodaciOGrupamaDelatnosti',
        'SviPodaciOGrupamaDelatnostiResponse' => 'App\\PL\\Production\\SviPodaciOGrupamaDelatnostiResponse',
        'SviPodaciOMestima' => 'App\\PL\\Production\\SviPodaciOMestima',
        'SviPodaciOMestimaResponse' => 'App\\PL\\Production\\SviPodaciOMestimaResponse',
        'SviPodaciOOblastimaDelatnosti' => 'App\\PL\\Production\\SviPodaciOOblastimaDelatnosti',
        'SviPodaciOOblastimaDelatnostiResponse' => 'App\\PL\\Production\\SviPodaciOOblastimaDelatnostiResponse',
        'SviPodaciOOblicimaOrganizovanjaZaduzbineIliFondacije' => 'App\\PL\\Production\\SviPodaciOOblicimaOrganizovanjaZaduzbineIliFondacije',
        'SviPodaciOOblicimaOrganizovanjaZaduzbineIliFondacijeResponse' => 'App\\PL\\Production\\SviPodaciOOblicimaOrganizovanjaZaduzbineIliFondacijeResponse',
        'SviPodaciOOdsecimaDelatnosti' => 'App\\PL\\Production\\SviPodaciOOdsecimaDelatnosti',
        'SviPodaciOOdsecimaDelatnostiResponse' => 'App\\PL\\Production\\SviPodaciOOdsecimaDelatnostiResponse',
        'SviPodaciOOkruzima' => 'App\\PL\\Production\\SviPodaciOOkruzima',
        'SviPodaciOOkruzimaResponse' => 'App\\PL\\Production\\SviPodaciOOkruzimaResponse',
        'SviPodaciOOpstinama' => 'App\\PL\\Production\\SviPodaciOOpstinama',
        'SviPodaciOOpstinamaResponse' => 'App\\PL\\Production\\SviPodaciOOpstinamaResponse',
        'SviPodaciOPravnimFormama' => 'App\\PL\\Production\\SviPodaciOPravnimFormama',
        'SviPodaciOPravnimFormamaResponse' => 'App\\PL\\Production\\SviPodaciOPravnimFormamaResponse',
        'SviPodaciOSektorimaDelatnosti' => 'App\\PL\\Production\\SviPodaciOSektorimaDelatnosti',
        'SviPodaciOSektorimaDelatnostiResponse' => 'App\\PL\\Production\\SviPodaciOSektorimaDelatnostiResponse',
        'SviPodaciOStatusimaPreduzetnika' => 'App\\PL\\Production\\SviPodaciOStatusimaPreduzetnika',
        'SviPodaciOStatusimaPreduzetnikaResponse' => 'App\\PL\\Production\\SviPodaciOStatusimaPreduzetnikaResponse',
        'SviPodaciOStatusimaPrivrednogDrustva' => 'App\\PL\\Production\\SviPodaciOStatusimaPrivrednogDrustva',
        'SviPodaciOStatusimaPrivrednogDrustvaResponse' => 'App\\PL\\Production\\SviPodaciOStatusimaPrivrednogDrustvaResponse',
        'SviPodaciOStatusimaUdruzenja' => 'App\\PL\\Production\\SviPodaciOStatusimaUdruzenja',
        'SviPodaciOStatusimaUdruzenjaResponse' => 'App\\PL\\Production\\SviPodaciOStatusimaUdruzenjaResponse',
        'SviPodaciOStatusimaZaduzbineIliFondacije' => 'App\\PL\\Production\\SviPodaciOStatusimaZaduzbineIliFondacije',
        'SviPodaciOStatusimaZaduzbineIliFondacijeResponse' => 'App\\PL\\Production\\SviPodaciOStatusimaZaduzbineIliFondacijeResponse',
        'SviPodaciOTipoviimaOblastiOstvarivanjaCiljevaUdruzenja' => 'App\\PL\\Production\\SviPodaciOTipoviimaOblastiOstvarivanjaCiljevaUdruzenja',
        'SviPodaciOTipoviimaOblastiOstvarivanjaCiljevaUdruzenjaResponse' => 'App\\PL\\Production\\SviPodaciOTipoviimaOblastiOstvarivanjaCiljevaUdruzenjaResponse',
        'SviPodaciOTipoviimaZabeleskiUdruzenja' => 'App\\PL\\Production\\SviPodaciOTipoviimaZabeleskiUdruzenja',
        'SviPodaciOTipoviimaZabeleskiUdruzenjaResponse' => 'App\\PL\\Production\\SviPodaciOTipoviimaZabeleskiUdruzenjaResponse',
        'SviPodaciOTipovimaCiljevaZaduzbineIliFondacije' => 'App\\PL\\Production\\SviPodaciOTipovimaCiljevaZaduzbineIliFondacije',
        'SviPodaciOTipovimaCiljevaZaduzbineIliFondacijeResponse' => 'App\\PL\\Production\\SviPodaciOTipovimaCiljevaZaduzbineIliFondacijeResponse',
        'SviPodaciOTipovimaClanaPrivrednogDrustva' => 'App\\PL\\Production\\SviPodaciOTipovimaClanaPrivrednogDrustva',
        'SviPodaciOTipovimaClanaPrivrednogDrustvaResponse' => 'App\\PL\\Production\\SviPodaciOTipovimaClanaPrivrednogDrustvaResponse',
        'SviPodaciOTipovimaFunkcijeZastupnikaZaduzbineIliFondacije' => 'App\\PL\\Production\\SviPodaciOTipovimaFunkcijeZastupnikaZaduzbineIliFondacije',
        'SviPodaciOTipovimaFunkcijeZastupnikaZaduzbineIliFondacijeResponse' => 'App\\PL\\Production\\SviPodaciOTipovimaFunkcijeZastupnikaZaduzbineIliFondacijeResponse',
        'SviPodaciOTipovimaJezika' => 'App\\PL\\Production\\SviPodaciOTipovimaJezika',
        'SviPodaciOTipovimaJezikaResponse' => 'App\\PL\\Production\\SviPodaciOTipovimaJezikaResponse',
        'SviPodaciOTipovimaKapitalaPrivrednogDrustva' => 'App\\PL\\Production\\SviPodaciOTipovimaKapitalaPrivrednogDrustva',
        'SviPodaciOTipovimaKapitalaPrivrednogDrustvaResponse' => 'App\\PL\\Production\\SviPodaciOTipovimaKapitalaPrivrednogDrustvaResponse',
        'SviPodaciOTipovimaKapitalaZaduzbineIliFondacije' => 'App\\PL\\Production\\SviPodaciOTipovimaKapitalaZaduzbineIliFondacije',
        'SviPodaciOTipovimaKapitalaZaduzbineIliFondacijeResponse' => 'App\\PL\\Production\\SviPodaciOTipovimaKapitalaZaduzbineIliFondacijeResponse',
        'SviPodaciOTipovimaLica' => 'App\\PL\\Production\\SviPodaciOTipovimaLica',
        'SviPodaciOTipovimaLicaResponse' => 'App\\PL\\Production\\SviPodaciOTipovimaLicaResponse',
        'SviPodaciOTipovimaNacinaPromene' => 'App\\PL\\Production\\SviPodaciOTipovimaNacinaPromene',
        'SviPodaciOTipovimaNacinaPromeneResponse' => 'App\\PL\\Production\\SviPodaciOTipovimaNacinaPromeneResponse',
        'SviPodaciOTipovimaNazivaUdruzenja' => 'App\\PL\\Production\\SviPodaciOTipovimaNazivaUdruzenja',
        'SviPodaciOTipovimaNazivaUdruzenjaResponse' => 'App\\PL\\Production\\SviPodaciOTipovimaNazivaUdruzenjaResponse',
        'SviPodaciOTipovimaPoslovnogSubjekta' => 'App\\PL\\Production\\SviPodaciOTipovimaPoslovnogSubjekta',
        'SviPodaciOTipovimaPoslovnogSubjektaResponse' => 'App\\PL\\Production\\SviPodaciOTipovimaPoslovnogSubjektaResponse',
        'SviPodaciOTipovimaRegistracionogPostupka' => 'App\\PL\\Production\\SviPodaciOTipovimaRegistracionogPostupka',
        'SviPodaciOTipovimaRegistracionogPostupkaResponse' => 'App\\PL\\Production\\SviPodaciOTipovimaRegistracionogPostupkaResponse',
        'SviPodaciOTipovimaZabeleskiZaduzbineIliFondacije' => 'App\\PL\\Production\\SviPodaciOTipovimaZabeleskiZaduzbineIliFondacije',
        'SviPodaciOTipovimaZabeleskiZaduzbineIliFondacijeResponse' => 'App\\PL\\Production\\SviPodaciOTipovimaZabeleskiZaduzbineIliFondacijeResponse',
        'SviPodaciOVelicinamaPoslovnogSubjekta' => 'App\\PL\\Production\\SviPodaciOVelicinamaPoslovnogSubjekta',
        'SviPodaciOVelicinamaPoslovnogSubjektaResponse' => 'App\\PL\\Production\\SviPodaciOVelicinamaPoslovnogSubjektaResponse',
        'SviPodaciOVrstamaZabelezbePreduzetnika' => 'App\\PL\\Production\\SviPodaciOVrstamaZabelezbePreduzetnika',
        'SviPodaciOVrstamaZabelezbePreduzetnikaResponse' => 'App\\PL\\Production\\SviPodaciOVrstamaZabelezbePreduzetnikaResponse',
        'SviPodaciOVrstamaZabelezbePrivrednogDrustva' => 'App\\PL\\Production\\SviPodaciOVrstamaZabelezbePrivrednogDrustva',
        'SviPodaciOVrstamaZabelezbePrivrednogDrustvaResponse' => 'App\\PL\\Production\\SviPodaciOVrstamaZabelezbePrivrednogDrustvaResponse',
        'SviPodaciOVrstamaZabelezbeSportskogUdruzenja' => 'App\\PL\\Production\\SviPodaciOVrstamaZabelezbeSportskogUdruzenja',
        'SviPodaciOVrstamaZabelezbeSportskogUdruzenjaResponse' => 'App\\PL\\Production\\SviPodaciOVrstamaZabelezbeSportskogUdruzenjaResponse',
        'SviPodaciOVrstamaOblikaOrganizovanjaSportskogUdruzenja' => 'App\\PL\\Production\\SviPodaciOVrstamaOblikaOrganizovanjaSportskogUdruzenja',
        'SviPodaciOVrstamaOblikaOrganizovanjaSportskogUdruzenjaResponse' => 'App\\PL\\Production\\SviPodaciOVrstamaOblikaOrganizovanjaSportskogUdruzenjaResponse',
        'SviPodaciOVrstamaGraneSporta' => 'App\\PL\\Production\\SviPodaciOVrstamaGraneSporta',
        'SviPodaciOVrstamaGraneSportaResponse' => 'App\\PL\\Production\\SviPodaciOVrstamaGraneSportaResponse',
        'SviPodaciOOblicimaOrganizovanjaUdruzenja' => 'App\\PL\\Production\\SviPodaciOOblicimaOrganizovanjaUdruzenja',
        'SviPodaciOOblicimaOrganizovanjaUdruzenjaResponse' => 'App\\PL\\Production\\SviPodaciOOblicimaOrganizovanjaUdruzenjaResponse',
        'SviPodaciOStatusimaKomore' => 'App\\PL\\Production\\SviPodaciOStatusimaKomore',
        'SviPodaciOStatusimaKomoreResponse' => 'App\\PL\\Production\\SviPodaciOStatusimaKomoreResponse',
        'SviPodaciOTipovimaNazivaKomore' => 'App\\PL\\Production\\SviPodaciOTipovimaNazivaKomore',
        'SviPodaciOTipovimaNazivaKomoreResponse' => 'App\\PL\\Production\\SviPodaciOTipovimaNazivaKomoreResponse',
        'SviPodaciOStatusimaStecajneMase' => 'App\\PL\\Production\\SviPodaciOStatusimaStecajneMase',
        'SviPodaciOStatusimaStecajneMaseResponse' => 'App\\PL\\Production\\SviPodaciOStatusimaStecajneMaseResponse',
        'SviPodaciOTipovimaOsnivaca' => 'App\\PL\\Production\\SviPodaciOTipovimaOsnivaca',
        'SviPodaciOTipovimaOsnivacaResponse' => 'App\\PL\\Production\\SviPodaciOTipovimaOsnivacaResponse',
        'SviPodaciOStatusimaSportskogUdruzenja' => 'App\\PL\\Production\\SviPodaciOStatusimaSportskogUdruzenja',
        'SviPodaciOStatusimaSportskogUdruzenjaResponse' => 'App\\PL\\Production\\SviPodaciOStatusimaSportskogUdruzenjaResponse',
        'SviPodaciONacinuNastankaStecajneMase' => 'App\\PL\\Production\\SviPodaciONacinuNastankaStecajneMase',
        'SviPodaciONacinuNastankaStecajneMaseResponse' => 'App\\PL\\Production\\SviPodaciONacinuNastankaStecajneMaseResponse',
        'SviPodaciOVrstiZadruge' => 'App\\PL\\Production\\SviPodaciOVrstiZadruge',
        'SviPodaciOVrstiZadrugeResponse' => 'App\\PL\\Production\\SviPodaciOVrstiZadrugeResponse',
        'SviPodaciODiscipliniSporta' => 'App\\PL\\Production\\SviPodaciODiscipliniSporta',
        'SviPodaciODiscipliniSportaResponse' => 'App\\PL\\Production\\SviPodaciODiscipliniSportaResponse',
        'SviPodaciOOblastiSporta' => 'App\\PL\\Production\\SviPodaciOOblastiSporta',
        'SviPodaciOOblastiSportaResponse' => 'App\\PL\\Production\\SviPodaciOOblastiSportaResponse',
        'SviPodaciORazloguBrisanja' => 'App\\PL\\Production\\SviPodaciORazloguBrisanja',
        'SviPodaciORazloguBrisanjaResponse' => 'App\\PL\\Production\\SviPodaciORazloguBrisanjaResponse',
    ];

    /**
     * @param  array  $options  A array of config values
     * @param  string  $wsdl  The wsdl file to use
     */
    public function __construct(array $options = [], $wsdl = null)
    /*{
      foreach (self::$classmap as $key => $value) {
        if (!isset($options['classmap'][$key])) {
          $options['classmap'][$key] = $value;
        }
      }
      $options = array_merge(array (
      'features' => 1,
    ), $options);
      if (!$wsdl) {
        $wsdl = 'https://service1.apr.gov.rs:4430/plws/PlService.svc?wsdl';
      }
      parent::__construct($wsdl, $options);
    }*/
    {
        foreach (self::$classmap as $key => $value) {
            if (! isset($options['classmap'][$key])) {
                $options['classmap'][$key] = $value;
            }
        }

        $options = array_merge([
            'features' => 1,
            'trace' => 1, // Enable tracing for debugging
            'exceptions' => true,
            'cache_wsdl' => WSDL_CACHE_NONE, // Disable caching
            'encoding' => 'utf-8',
            'stream_context' => stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ],
            ]),
        ], $options);

        if (! $wsdl) {
            $wsdl = 'https://service1.apr.gov.rs:4430/plws/PlService.svc?wsdl';
        }
        $username = 'sbrs.plws';
        $password = 'f[2tC#';
        $wsseHeader = new \SoapHeader(
            'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd',
            'Security',
            new \SoapVar(
                '<wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
                <wsse:UsernameToken>
                    <wsse:Username>'.htmlspecialchars($username, ENT_XML1, 'UTF-8').'</wsse:Username>
                    <wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">'.htmlspecialchars($password, ENT_XML1, 'UTF-8').'</wsse:Password>
                </wsse:UsernameToken>
            </wsse:Security>',
                XSD_ANYXML
            )
        );
        $this->__setSoapHeaders($wsseHeader);
        parent::__construct($wsdl, $options);
    }

    /**
     * @return PreuzmiPodatkeOPrivrednomSubjektuResponse
     */
    public function PreuzmiPodatkeOPrivrednomSubjektu(PreuzmiPodatkeOPrivrednomSubjektu $parameters)
    {
        return $this->__soapCall('PreuzmiPodatkeOPrivrednomSubjektu', [$parameters]);
    }

    /**
     * @return PreuzmiPromenePodatakaOPrivrednimSubjektimaResponse
     */
    public function PreuzmiPromenePodatakaOPrivrednimSubjektima(PreuzmiPromenePodatakaOPrivrednimSubjektima $parameters)
    {
        return $this->__soapCall('PreuzmiPromenePodatakaOPrivrednimSubjektima', [$parameters]);
    }

    /**
     * @return SviPodaciODelatnostimaResponse
     */
    public function SviPodaciODelatnostima(SviPodaciODelatnostima $parameters)
    {
        return $this->__soapCall('SviPodaciODelatnostima', [$parameters]);
    }

    /**
     * @return SviPodaciODrzavamaResponse
     */
    public function SviPodaciODrzavama(SviPodaciODrzavama $parameters)
    {
        return $this->__soapCall('SviPodaciODrzavama', [$parameters]);
    }

    /**
     * @return SviPodaciOFunkcijamaOsnivacaZaduzbineIliFondacijeResponse
     */
    public function SviPodaciOFunkcijamaOsnivacaZaduzbineIliFondacije(SviPodaciOFunkcijamaOsnivacaZaduzbineIliFondacije $parameters)
    {
        return $this->__soapCall('SviPodaciOFunkcijamaOsnivacaZaduzbineIliFondacije', [$parameters]);
    }

    /**
     * @return SviPodaciOFunkcijamaZakonskogZastupnikaPrivrednogDrustvaResponse
     */
    public function SviPodaciOFunkcijamaZakonskogZastupnikaPrivrednogDrustva(SviPodaciOFunkcijamaZakonskogZastupnikaPrivrednogDrustva $parameters)
    {
        return $this->__soapCall('SviPodaciOFunkcijamaZakonskogZastupnikaPrivrednogDrustva', [$parameters]);
    }

    /**
     * @return SviPodaciOGrupamaDelatnostiResponse
     */
    public function SviPodaciOGrupamaDelatnosti(SviPodaciOGrupamaDelatnosti $parameters)
    {
        return $this->__soapCall('SviPodaciOGrupamaDelatnosti', [$parameters]);
    }

    /**
     * @return SviPodaciOMestimaResponse
     */
    public function SviPodaciOMestima(SviPodaciOMestima $parameters)
    {
        return $this->__soapCall('SviPodaciOMestima', [$parameters]);
    }

    /**
     * @return SviPodaciOOblastimaDelatnostiResponse
     */
    public function SviPodaciOOblastimaDelatnosti(SviPodaciOOblastimaDelatnosti $parameters)
    {
        return $this->__soapCall('SviPodaciOOblastimaDelatnosti', [$parameters]);
    }

    /**
     * @return SviPodaciOOblicimaOrganizovanjaZaduzbineIliFondacijeResponse
     */
    public function SviPodaciOOblicimaOrganizovanjaZaduzbineIliFondacije(SviPodaciOOblicimaOrganizovanjaZaduzbineIliFondacije $parameters)
    {
        return $this->__soapCall('SviPodaciOOblicimaOrganizovanjaZaduzbineIliFondacije', [$parameters]);
    }

    /**
     * @return SviPodaciOOdsecimaDelatnostiResponse
     */
    public function SviPodaciOOdsecimaDelatnosti(SviPodaciOOdsecimaDelatnosti $parameters)
    {
        return $this->__soapCall('SviPodaciOOdsecimaDelatnosti', [$parameters]);
    }

    /**
     * @return SviPodaciOOkruzimaResponse
     */
    public function SviPodaciOOkruzima(SviPodaciOOkruzima $parameters)
    {
        return $this->__soapCall('SviPodaciOOkruzima', [$parameters]);
    }

    /**
     * @return SviPodaciOOpstinamaResponse
     */
    public function SviPodaciOOpstinama(SviPodaciOOpstinama $parameters)
    {
        return $this->__soapCall('SviPodaciOOpstinama', [$parameters]);
    }

    /**
     * @return SviPodaciOPravnimFormamaResponse
     */
    public function SviPodaciOPravnimFormama(SviPodaciOPravnimFormama $parameters)
    {
        return $this->__soapCall('SviPodaciOPravnimFormama', [$parameters]);
    }

    /**
     * @return SviPodaciOSektorimaDelatnostiResponse
     */
    public function SviPodaciOSektorimaDelatnosti(SviPodaciOSektorimaDelatnosti $parameters)
    {
        return $this->__soapCall('SviPodaciOSektorimaDelatnosti', [$parameters]);
    }

    /**
     * @return SviPodaciOStatusimaPreduzetnikaResponse
     */
    public function SviPodaciOStatusimaPreduzetnika(SviPodaciOStatusimaPreduzetnika $parameters)
    {
        return $this->__soapCall('SviPodaciOStatusimaPreduzetnika', [$parameters]);
    }

    /**
     * @return SviPodaciOStatusimaPrivrednogDrustvaResponse
     */
    public function SviPodaciOStatusimaPrivrednogDrustva(SviPodaciOStatusimaPrivrednogDrustva $parameters)
    {
        return $this->__soapCall('SviPodaciOStatusimaPrivrednogDrustva', [$parameters]);
    }

    /**
     * @return SviPodaciOStatusimaUdruzenjaResponse
     */
    public function SviPodaciOStatusimaUdruzenja(SviPodaciOStatusimaUdruzenja $parameters)
    {
        return $this->__soapCall('SviPodaciOStatusimaUdruzenja', [$parameters]);
    }

    /**
     * @return SviPodaciOStatusimaZaduzbineIliFondacijeResponse
     */
    public function SviPodaciOStatusimaZaduzbineIliFondacije(SviPodaciOStatusimaZaduzbineIliFondacije $parameters)
    {
        return $this->__soapCall('SviPodaciOStatusimaZaduzbineIliFondacije', [$parameters]);
    }

    /**
     * @return SviPodaciOTipoviimaOblastiOstvarivanjaCiljevaUdruzenjaResponse
     */
    public function SviPodaciOTipoviimaOblastiOstvarivanjaCiljevaUdruzenja(SviPodaciOTipoviimaOblastiOstvarivanjaCiljevaUdruzenja $parameters)
    {
        return $this->__soapCall('SviPodaciOTipoviimaOblastiOstvarivanjaCiljevaUdruzenja', [$parameters]);
    }

    /**
     * @return SviPodaciOTipoviimaZabeleskiUdruzenjaResponse
     */
    public function SviPodaciOTipoviimaZabeleskiUdruzenja(SviPodaciOTipoviimaZabeleskiUdruzenja $parameters)
    {
        return $this->__soapCall('SviPodaciOTipoviimaZabeleskiUdruzenja', [$parameters]);
    }

    /**
     * @return SviPodaciOTipovimaCiljevaZaduzbineIliFondacijeResponse
     */
    public function SviPodaciOTipovimaCiljevaZaduzbineIliFondacije(SviPodaciOTipovimaCiljevaZaduzbineIliFondacije $parameters)
    {
        return $this->__soapCall('SviPodaciOTipovimaCiljevaZaduzbineIliFondacije', [$parameters]);
    }

    /**
     * @return SviPodaciOTipovimaClanaPrivrednogDrustvaResponse
     */
    public function SviPodaciOTipovimaClanaPrivrednogDrustva(SviPodaciOTipovimaClanaPrivrednogDrustva $parameters)
    {
        return $this->__soapCall('SviPodaciOTipovimaClanaPrivrednogDrustva', [$parameters]);
    }

    /**
     * @return SviPodaciOTipovimaFunkcijeZastupnikaZaduzbineIliFondacijeResponse
     */
    public function SviPodaciOTipovimaFunkcijeZastupnikaZaduzbineIliFondacije(SviPodaciOTipovimaFunkcijeZastupnikaZaduzbineIliFondacije $parameters)
    {
        return $this->__soapCall('SviPodaciOTipovimaFunkcijeZastupnikaZaduzbineIliFondacije', [$parameters]);
    }

    /**
     * @return SviPodaciOTipovimaJezikaResponse
     */
    public function SviPodaciOTipovimaJezika(SviPodaciOTipovimaJezika $parameters)
    {
        return $this->__soapCall('SviPodaciOTipovimaJezika', [$parameters]);
    }

    /**
     * @return SviPodaciOTipovimaKapitalaPrivrednogDrustvaResponse
     */
    public function SviPodaciOTipovimaKapitalaPrivrednogDrustva(SviPodaciOTipovimaKapitalaPrivrednogDrustva $parameters)
    {
        return $this->__soapCall('SviPodaciOTipovimaKapitalaPrivrednogDrustva', [$parameters]);
    }

    /**
     * @return SviPodaciOTipovimaKapitalaZaduzbineIliFondacijeResponse
     */
    public function SviPodaciOTipovimaKapitalaZaduzbineIliFondacije(SviPodaciOTipovimaKapitalaZaduzbineIliFondacije $parameters)
    {
        return $this->__soapCall('SviPodaciOTipovimaKapitalaZaduzbineIliFondacije', [$parameters]);
    }

    /**
     * @return SviPodaciOTipovimaLicaResponse
     */
    public function SviPodaciOTipovimaLica(SviPodaciOTipovimaLica $parameters)
    {
        return $this->__soapCall('SviPodaciOTipovimaLica', [$parameters]);
    }

    /**
     * @return SviPodaciOTipovimaNacinaPromeneResponse
     */
    public function SviPodaciOTipovimaNacinaPromene(SviPodaciOTipovimaNacinaPromene $parameters)
    {
        return $this->__soapCall('SviPodaciOTipovimaNacinaPromene', [$parameters]);
    }

    /**
     * @return SviPodaciOTipovimaNazivaUdruzenjaResponse
     */
    public function SviPodaciOTipovimaNazivaUdruzenja(SviPodaciOTipovimaNazivaUdruzenja $parameters)
    {
        return $this->__soapCall('SviPodaciOTipovimaNazivaUdruzenja', [$parameters]);
    }

    /**
     * @return SviPodaciOTipovimaPoslovnogSubjektaResponse
     */
    public function SviPodaciOTipovimaPoslovnogSubjekta(SviPodaciOTipovimaPoslovnogSubjekta $parameters)
    {
        return $this->__soapCall('SviPodaciOTipovimaPoslovnogSubjekta', [$parameters]);
    }

    /**
     * @return SviPodaciOTipovimaRegistracionogPostupkaResponse
     */
    public function SviPodaciOTipovimaRegistracionogPostupka(SviPodaciOTipovimaRegistracionogPostupka $parameters)
    {
        return $this->__soapCall('SviPodaciOTipovimaRegistracionogPostupka', [$parameters]);
    }

    /**
     * @return SviPodaciOTipovimaZabeleskiZaduzbineIliFondacijeResponse
     */
    public function SviPodaciOTipovimaZabeleskiZaduzbineIliFondacije(SviPodaciOTipovimaZabeleskiZaduzbineIliFondacije $parameters)
    {
        return $this->__soapCall('SviPodaciOTipovimaZabeleskiZaduzbineIliFondacije', [$parameters]);
    }

    /**
     * @return SviPodaciOVelicinamaPoslovnogSubjektaResponse
     */
    public function SviPodaciOVelicinamaPoslovnogSubjekta(SviPodaciOVelicinamaPoslovnogSubjekta $parameters)
    {
        return $this->__soapCall('SviPodaciOVelicinamaPoslovnogSubjekta', [$parameters]);
    }

    /**
     * @return SviPodaciOVrstamaZabelezbePreduzetnikaResponse
     */
    public function SviPodaciOVrstamaZabelezbePreduzetnika(SviPodaciOVrstamaZabelezbePreduzetnika $parameters)
    {
        return $this->__soapCall('SviPodaciOVrstamaZabelezbePreduzetnika', [$parameters]);
    }

    /**
     * @return SviPodaciOVrstamaZabelezbePrivrednogDrustvaResponse
     */
    public function SviPodaciOVrstamaZabelezbePrivrednogDrustva(SviPodaciOVrstamaZabelezbePrivrednogDrustva $parameters)
    {
        return $this->__soapCall('SviPodaciOVrstamaZabelezbePrivrednogDrustva', [$parameters]);
    }

    /**
     * @return SviPodaciOVrstamaZabelezbeSportskogUdruzenjaResponse
     */
    public function SviPodaciOVrstamaZabelezbeSportskogUdruzenja(SviPodaciOVrstamaZabelezbeSportskogUdruzenja $parameters)
    {
        return $this->__soapCall('SviPodaciOVrstamaZabelezbeSportskogUdruzenja', [$parameters]);
    }

    /**
     * @return SviPodaciOVrstamaOblikaOrganizovanjaSportskogUdruzenjaResponse
     */
    public function SviPodaciOVrstamaOblikaOrganizovanjaSportskogUdruzenja(SviPodaciOVrstamaOblikaOrganizovanjaSportskogUdruzenja $parameters)
    {
        return $this->__soapCall('SviPodaciOVrstamaOblikaOrganizovanjaSportskogUdruzenja', [$parameters]);
    }

    /**
     * @return SviPodaciOVrstamaGraneSportaResponse
     */
    public function SviPodaciOVrstamaGraneSporta(SviPodaciOVrstamaGraneSporta $parameters)
    {
        return $this->__soapCall('SviPodaciOVrstamaGraneSporta', [$parameters]);
    }

    /**
     * @return SviPodaciOOblicimaOrganizovanjaUdruzenjaResponse
     */
    public function SviPodaciOOblicimaOrganizovanjaUdruzenja(SviPodaciOOblicimaOrganizovanjaUdruzenja $parameters)
    {
        return $this->__soapCall('SviPodaciOOblicimaOrganizovanjaUdruzenja', [$parameters]);
    }

    /**
     * @return SviPodaciOStatusimaKomoreResponse
     */
    public function SviPodaciOStatusimaKomore(SviPodaciOStatusimaKomore $parameters)
    {
        return $this->__soapCall('SviPodaciOStatusimaKomore', [$parameters]);
    }

    /**
     * @return SviPodaciOTipovimaNazivaKomoreResponse
     */
    public function SviPodaciOTipovimaNazivaKomore(SviPodaciOTipovimaNazivaKomore $parameters)
    {
        return $this->__soapCall('SviPodaciOTipovimaNazivaKomore', [$parameters]);
    }

    /**
     * @return SviPodaciOStatusimaStecajneMaseResponse
     */
    public function SviPodaciOStatusimaStecajneMase(SviPodaciOStatusimaStecajneMase $parameters)
    {
        return $this->__soapCall('SviPodaciOStatusimaStecajneMase', [$parameters]);
    }

    /**
     * @return SviPodaciOTipovimaOsnivacaResponse
     */
    public function SviPodaciOTipovimaOsnivaca(SviPodaciOTipovimaOsnivaca $parameters)
    {
        return $this->__soapCall('SviPodaciOTipovimaOsnivaca', [$parameters]);
    }

    /**
     * @return SviPodaciOStatusimaSportskogUdruzenjaResponse
     */
    public function SviPodaciOStatusimaSportskogUdruzenja(SviPodaciOStatusimaSportskogUdruzenja $parameters)
    {
        return $this->__soapCall('SviPodaciOStatusimaSportskogUdruzenja', [$parameters]);
    }

    /**
     * @return SviPodaciONacinuNastankaStecajneMaseResponse
     */
    public function SviPodaciONacinuNastankaStecajneMase(SviPodaciONacinuNastankaStecajneMase $parameters)
    {
        return $this->__soapCall('SviPodaciONacinuNastankaStecajneMase', [$parameters]);
    }

    /**
     * @return SviPodaciOVrstiZadrugeResponse
     */
    public function SviPodaciOVrstiZadruge(SviPodaciOVrstiZadruge $parameters)
    {
        return $this->__soapCall('SviPodaciOVrstiZadruge', [$parameters]);
    }

    /**
     * @return SviPodaciODiscipliniSportaReponse
     */
    public function SviPodaciODiscipliniSporta(SviPodaciODiscipliniSporta $parameters)
    {
        return $this->__soapCall('SviPodaciODiscipliniSporta', [$parameters]);
    }

    /**
     * @return SviPodaciOOblastiSportaReponse
     */
    public function SviPodaciOOblastiSporta(SviPodaciOOblastiSporta $parameters)
    {
        return $this->__soapCall('SviPodaciOOblastiSporta', [$parameters]);
    }

    /**
     * @return SviPodaciORazloguBrisanjaReponse
     */
    public function SviPodaciORazloguBrisanja(SviPodaciORazloguBrisanja $parameters)
    {
        return $this->__soapCall('SviPodaciORazloguBrisanja', [$parameters]);
    }
}
