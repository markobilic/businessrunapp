<?php
namespace App\PL\Production;


class PlService extends \SoapClient
{

    /**
     * @var array $classmap The defined classes
     */
    private static $classmap = array (
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
    );

    /**
     * @param array $options A array of config values
     * @param string $wsdl The wsdl file to use
     */
    public function __construct(array $options = array(), $wsdl = null)
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
        if (!isset($options['classmap'][$key])) {
          $options['classmap'][$key] = $value;
        }
      }
      
      $options = array_merge(array(
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
      ), $options);

      if (!$wsdl) {
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
                    <wsse:Username>' . htmlspecialchars($username, ENT_XML1, 'UTF-8') . '</wsse:Username>
                    <wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">' . htmlspecialchars($password, ENT_XML1, 'UTF-8') . '</wsse:Password>
                </wsse:UsernameToken>
            </wsse:Security>', 
            XSD_ANYXML
        )
      );
      $this->__setSoapHeaders($wsseHeader);
      parent::__construct($wsdl, $options);
    }

    /**
     * @param PreuzmiPodatkeOPrivrednomSubjektu $parameters
     * @return PreuzmiPodatkeOPrivrednomSubjektuResponse
     */
    public function PreuzmiPodatkeOPrivrednomSubjektu(PreuzmiPodatkeOPrivrednomSubjektu $parameters)
    {
      return $this->__soapCall('PreuzmiPodatkeOPrivrednomSubjektu', array($parameters));
    }

    /**
     * @param PreuzmiPromenePodatakaOPrivrednimSubjektima $parameters
     * @return PreuzmiPromenePodatakaOPrivrednimSubjektimaResponse
     */
    public function PreuzmiPromenePodatakaOPrivrednimSubjektima(PreuzmiPromenePodatakaOPrivrednimSubjektima $parameters)
    {
      return $this->__soapCall('PreuzmiPromenePodatakaOPrivrednimSubjektima', array($parameters));
    }

    /**
     * @param SviPodaciODelatnostima $parameters
     * @return SviPodaciODelatnostimaResponse
     */
    public function SviPodaciODelatnostima(SviPodaciODelatnostima $parameters)
    {
      return $this->__soapCall('SviPodaciODelatnostima', array($parameters));
    }

    /**
     * @param SviPodaciODrzavama $parameters
     * @return SviPodaciODrzavamaResponse
     */
    public function SviPodaciODrzavama(SviPodaciODrzavama $parameters)
    {
      return $this->__soapCall('SviPodaciODrzavama', array($parameters));
    }

    /**
     * @param SviPodaciOFunkcijamaOsnivacaZaduzbineIliFondacije $parameters
     * @return SviPodaciOFunkcijamaOsnivacaZaduzbineIliFondacijeResponse
     */
    public function SviPodaciOFunkcijamaOsnivacaZaduzbineIliFondacije(SviPodaciOFunkcijamaOsnivacaZaduzbineIliFondacije $parameters)
    {
      return $this->__soapCall('SviPodaciOFunkcijamaOsnivacaZaduzbineIliFondacije', array($parameters));
    }

    /**
     * @param SviPodaciOFunkcijamaZakonskogZastupnikaPrivrednogDrustva $parameters
     * @return SviPodaciOFunkcijamaZakonskogZastupnikaPrivrednogDrustvaResponse
     */
    public function SviPodaciOFunkcijamaZakonskogZastupnikaPrivrednogDrustva(SviPodaciOFunkcijamaZakonskogZastupnikaPrivrednogDrustva $parameters)
    {
      return $this->__soapCall('SviPodaciOFunkcijamaZakonskogZastupnikaPrivrednogDrustva', array($parameters));
    }

    /**
     * @param SviPodaciOGrupamaDelatnosti $parameters
     * @return SviPodaciOGrupamaDelatnostiResponse
     */
    public function SviPodaciOGrupamaDelatnosti(SviPodaciOGrupamaDelatnosti $parameters)
    {
      return $this->__soapCall('SviPodaciOGrupamaDelatnosti', array($parameters));
    }

    /**
     * @param SviPodaciOMestima $parameters
     * @return SviPodaciOMestimaResponse
     */
    public function SviPodaciOMestima(SviPodaciOMestima $parameters)
    {
      return $this->__soapCall('SviPodaciOMestima', array($parameters));
    }

    /**
     * @param SviPodaciOOblastimaDelatnosti $parameters
     * @return SviPodaciOOblastimaDelatnostiResponse
     */
    public function SviPodaciOOblastimaDelatnosti(SviPodaciOOblastimaDelatnosti $parameters)
    {
      return $this->__soapCall('SviPodaciOOblastimaDelatnosti', array($parameters));
    }

    /**
     * @param SviPodaciOOblicimaOrganizovanjaZaduzbineIliFondacije $parameters
     * @return SviPodaciOOblicimaOrganizovanjaZaduzbineIliFondacijeResponse
     */
    public function SviPodaciOOblicimaOrganizovanjaZaduzbineIliFondacije(SviPodaciOOblicimaOrganizovanjaZaduzbineIliFondacije $parameters)
    {
      return $this->__soapCall('SviPodaciOOblicimaOrganizovanjaZaduzbineIliFondacije', array($parameters));
    }

    /**
     * @param SviPodaciOOdsecimaDelatnosti $parameters
     * @return SviPodaciOOdsecimaDelatnostiResponse
     */
    public function SviPodaciOOdsecimaDelatnosti(SviPodaciOOdsecimaDelatnosti $parameters)
    {
      return $this->__soapCall('SviPodaciOOdsecimaDelatnosti', array($parameters));
    }

    /**
     * @param SviPodaciOOkruzima $parameters
     * @return SviPodaciOOkruzimaResponse
     */
    public function SviPodaciOOkruzima(SviPodaciOOkruzima $parameters)
    {
      return $this->__soapCall('SviPodaciOOkruzima', array($parameters));
    }

    /**
     * @param SviPodaciOOpstinama $parameters
     * @return SviPodaciOOpstinamaResponse
     */
    public function SviPodaciOOpstinama(SviPodaciOOpstinama $parameters)
    {
      return $this->__soapCall('SviPodaciOOpstinama', array($parameters));
    }

    /**
     * @param SviPodaciOPravnimFormama $parameters
     * @return SviPodaciOPravnimFormamaResponse
     */
    public function SviPodaciOPravnimFormama(SviPodaciOPravnimFormama $parameters)
    {
      return $this->__soapCall('SviPodaciOPravnimFormama', array($parameters));
    }

    /**
     * @param SviPodaciOSektorimaDelatnosti $parameters
     * @return SviPodaciOSektorimaDelatnostiResponse
     */
    public function SviPodaciOSektorimaDelatnosti(SviPodaciOSektorimaDelatnosti $parameters)
    {
      return $this->__soapCall('SviPodaciOSektorimaDelatnosti', array($parameters));
    }

    /**
     * @param SviPodaciOStatusimaPreduzetnika $parameters
     * @return SviPodaciOStatusimaPreduzetnikaResponse
     */
    public function SviPodaciOStatusimaPreduzetnika(SviPodaciOStatusimaPreduzetnika $parameters)
    {
      return $this->__soapCall('SviPodaciOStatusimaPreduzetnika', array($parameters));
    }

    /**
     * @param SviPodaciOStatusimaPrivrednogDrustva $parameters
     * @return SviPodaciOStatusimaPrivrednogDrustvaResponse
     */
    public function SviPodaciOStatusimaPrivrednogDrustva(SviPodaciOStatusimaPrivrednogDrustva $parameters)
    {
      return $this->__soapCall('SviPodaciOStatusimaPrivrednogDrustva', array($parameters));
    }

    /**
     * @param SviPodaciOStatusimaUdruzenja $parameters
     * @return SviPodaciOStatusimaUdruzenjaResponse
     */
    public function SviPodaciOStatusimaUdruzenja(SviPodaciOStatusimaUdruzenja $parameters)
    {
      return $this->__soapCall('SviPodaciOStatusimaUdruzenja', array($parameters));
    }

    /**
     * @param SviPodaciOStatusimaZaduzbineIliFondacije $parameters
     * @return SviPodaciOStatusimaZaduzbineIliFondacijeResponse
     */
    public function SviPodaciOStatusimaZaduzbineIliFondacije(SviPodaciOStatusimaZaduzbineIliFondacije $parameters)
    {
      return $this->__soapCall('SviPodaciOStatusimaZaduzbineIliFondacije', array($parameters));
    }

    /**
     * @param SviPodaciOTipoviimaOblastiOstvarivanjaCiljevaUdruzenja $parameters
     * @return SviPodaciOTipoviimaOblastiOstvarivanjaCiljevaUdruzenjaResponse
     */
    public function SviPodaciOTipoviimaOblastiOstvarivanjaCiljevaUdruzenja(SviPodaciOTipoviimaOblastiOstvarivanjaCiljevaUdruzenja $parameters)
    {
      return $this->__soapCall('SviPodaciOTipoviimaOblastiOstvarivanjaCiljevaUdruzenja', array($parameters));
    }

    /**
     * @param SviPodaciOTipoviimaZabeleskiUdruzenja $parameters
     * @return SviPodaciOTipoviimaZabeleskiUdruzenjaResponse
     */
    public function SviPodaciOTipoviimaZabeleskiUdruzenja(SviPodaciOTipoviimaZabeleskiUdruzenja $parameters)
    {
      return $this->__soapCall('SviPodaciOTipoviimaZabeleskiUdruzenja', array($parameters));
    }

    /**
     * @param SviPodaciOTipovimaCiljevaZaduzbineIliFondacije $parameters
     * @return SviPodaciOTipovimaCiljevaZaduzbineIliFondacijeResponse
     */
    public function SviPodaciOTipovimaCiljevaZaduzbineIliFondacije(SviPodaciOTipovimaCiljevaZaduzbineIliFondacije $parameters)
    {
      return $this->__soapCall('SviPodaciOTipovimaCiljevaZaduzbineIliFondacije', array($parameters));
    }

    /**
     * @param SviPodaciOTipovimaClanaPrivrednogDrustva $parameters
     * @return SviPodaciOTipovimaClanaPrivrednogDrustvaResponse
     */
    public function SviPodaciOTipovimaClanaPrivrednogDrustva(SviPodaciOTipovimaClanaPrivrednogDrustva $parameters)
    {
      return $this->__soapCall('SviPodaciOTipovimaClanaPrivrednogDrustva', array($parameters));
    }

    /**
     * @param SviPodaciOTipovimaFunkcijeZastupnikaZaduzbineIliFondacije $parameters
     * @return SviPodaciOTipovimaFunkcijeZastupnikaZaduzbineIliFondacijeResponse
     */
    public function SviPodaciOTipovimaFunkcijeZastupnikaZaduzbineIliFondacije(SviPodaciOTipovimaFunkcijeZastupnikaZaduzbineIliFondacije $parameters)
    {
      return $this->__soapCall('SviPodaciOTipovimaFunkcijeZastupnikaZaduzbineIliFondacije', array($parameters));
    }

    /**
     * @param SviPodaciOTipovimaJezika $parameters
     * @return SviPodaciOTipovimaJezikaResponse
     */
    public function SviPodaciOTipovimaJezika(SviPodaciOTipovimaJezika $parameters)
    {
      return $this->__soapCall('SviPodaciOTipovimaJezika', array($parameters));
    }

    /**
     * @param SviPodaciOTipovimaKapitalaPrivrednogDrustva $parameters
     * @return SviPodaciOTipovimaKapitalaPrivrednogDrustvaResponse
     */
    public function SviPodaciOTipovimaKapitalaPrivrednogDrustva(SviPodaciOTipovimaKapitalaPrivrednogDrustva $parameters)
    {
      return $this->__soapCall('SviPodaciOTipovimaKapitalaPrivrednogDrustva', array($parameters));
    }

    /**
     * @param SviPodaciOTipovimaKapitalaZaduzbineIliFondacije $parameters
     * @return SviPodaciOTipovimaKapitalaZaduzbineIliFondacijeResponse
     */
    public function SviPodaciOTipovimaKapitalaZaduzbineIliFondacije(SviPodaciOTipovimaKapitalaZaduzbineIliFondacije $parameters)
    {
      return $this->__soapCall('SviPodaciOTipovimaKapitalaZaduzbineIliFondacije', array($parameters));
    }

    /**
     * @param SviPodaciOTipovimaLica $parameters
     * @return SviPodaciOTipovimaLicaResponse
     */
    public function SviPodaciOTipovimaLica(SviPodaciOTipovimaLica $parameters)
    {
      return $this->__soapCall('SviPodaciOTipovimaLica', array($parameters));
    }

    /**
     * @param SviPodaciOTipovimaNacinaPromene $parameters
     * @return SviPodaciOTipovimaNacinaPromeneResponse
     */
    public function SviPodaciOTipovimaNacinaPromene(SviPodaciOTipovimaNacinaPromene $parameters)
    {
      return $this->__soapCall('SviPodaciOTipovimaNacinaPromene', array($parameters));
    }

    /**
     * @param SviPodaciOTipovimaNazivaUdruzenja $parameters
     * @return SviPodaciOTipovimaNazivaUdruzenjaResponse
     */
    public function SviPodaciOTipovimaNazivaUdruzenja(SviPodaciOTipovimaNazivaUdruzenja $parameters)
    {
      return $this->__soapCall('SviPodaciOTipovimaNazivaUdruzenja', array($parameters));
    }

    /**
     * @param SviPodaciOTipovimaPoslovnogSubjekta $parameters
     * @return SviPodaciOTipovimaPoslovnogSubjektaResponse
     */
    public function SviPodaciOTipovimaPoslovnogSubjekta(SviPodaciOTipovimaPoslovnogSubjekta $parameters)
    {
      return $this->__soapCall('SviPodaciOTipovimaPoslovnogSubjekta', array($parameters));
    }

    /**
     * @param SviPodaciOTipovimaRegistracionogPostupka $parameters
     * @return SviPodaciOTipovimaRegistracionogPostupkaResponse
     */
    public function SviPodaciOTipovimaRegistracionogPostupka(SviPodaciOTipovimaRegistracionogPostupka $parameters)
    {
      return $this->__soapCall('SviPodaciOTipovimaRegistracionogPostupka', array($parameters));
    }

    /**
     * @param SviPodaciOTipovimaZabeleskiZaduzbineIliFondacije $parameters
     * @return SviPodaciOTipovimaZabeleskiZaduzbineIliFondacijeResponse
     */
    public function SviPodaciOTipovimaZabeleskiZaduzbineIliFondacije(SviPodaciOTipovimaZabeleskiZaduzbineIliFondacije $parameters)
    {
      return $this->__soapCall('SviPodaciOTipovimaZabeleskiZaduzbineIliFondacije', array($parameters));
    }

    /**
     * @param SviPodaciOVelicinamaPoslovnogSubjekta $parameters
     * @return SviPodaciOVelicinamaPoslovnogSubjektaResponse
     */
    public function SviPodaciOVelicinamaPoslovnogSubjekta(SviPodaciOVelicinamaPoslovnogSubjekta $parameters)
    {
      return $this->__soapCall('SviPodaciOVelicinamaPoslovnogSubjekta', array($parameters));
    }

    /**
     * @param SviPodaciOVrstamaZabelezbePreduzetnika $parameters
     * @return SviPodaciOVrstamaZabelezbePreduzetnikaResponse
     */
    public function SviPodaciOVrstamaZabelezbePreduzetnika(SviPodaciOVrstamaZabelezbePreduzetnika $parameters)
    {
      return $this->__soapCall('SviPodaciOVrstamaZabelezbePreduzetnika', array($parameters));
    }

    /**
     * @param SviPodaciOVrstamaZabelezbePrivrednogDrustva $parameters
     * @return SviPodaciOVrstamaZabelezbePrivrednogDrustvaResponse
     */
    public function SviPodaciOVrstamaZabelezbePrivrednogDrustva(SviPodaciOVrstamaZabelezbePrivrednogDrustva $parameters)
    {
      return $this->__soapCall('SviPodaciOVrstamaZabelezbePrivrednogDrustva', array($parameters));
    }

    /**
     * @param SviPodaciOVrstamaZabelezbeSportskogUdruzenja $parameters
     * @return SviPodaciOVrstamaZabelezbeSportskogUdruzenjaResponse
     */
    public function SviPodaciOVrstamaZabelezbeSportskogUdruzenja(SviPodaciOVrstamaZabelezbeSportskogUdruzenja $parameters)
    {
      return $this->__soapCall('SviPodaciOVrstamaZabelezbeSportskogUdruzenja', array($parameters));
    }

    /**
     * @param SviPodaciOVrstamaOblikaOrganizovanjaSportskogUdruzenja $parameters
     * @return SviPodaciOVrstamaOblikaOrganizovanjaSportskogUdruzenjaResponse
     */
    public function SviPodaciOVrstamaOblikaOrganizovanjaSportskogUdruzenja(SviPodaciOVrstamaOblikaOrganizovanjaSportskogUdruzenja $parameters)
    {
      return $this->__soapCall('SviPodaciOVrstamaOblikaOrganizovanjaSportskogUdruzenja', array($parameters));
    }

    /**
     * @param SviPodaciOVrstamaGraneSporta $parameters
     * @return SviPodaciOVrstamaGraneSportaResponse
     */
    public function SviPodaciOVrstamaGraneSporta(SviPodaciOVrstamaGraneSporta $parameters)
    {
      return $this->__soapCall('SviPodaciOVrstamaGraneSporta', array($parameters));
    }

    /**
     * @param SviPodaciOOblicimaOrganizovanjaUdruzenja $parameters
     * @return SviPodaciOOblicimaOrganizovanjaUdruzenjaResponse
     */
    public function SviPodaciOOblicimaOrganizovanjaUdruzenja(SviPodaciOOblicimaOrganizovanjaUdruzenja $parameters)
    {
      return $this->__soapCall('SviPodaciOOblicimaOrganizovanjaUdruzenja', array($parameters));
    }

    /**
     * @param SviPodaciOStatusimaKomore $parameters
     * @return SviPodaciOStatusimaKomoreResponse
     */
    public function SviPodaciOStatusimaKomore(SviPodaciOStatusimaKomore $parameters)
    {
      return $this->__soapCall('SviPodaciOStatusimaKomore', array($parameters));
    }

    /**
     * @param SviPodaciOTipovimaNazivaKomore $parameters
     * @return SviPodaciOTipovimaNazivaKomoreResponse
     */
    public function SviPodaciOTipovimaNazivaKomore(SviPodaciOTipovimaNazivaKomore $parameters)
    {
      return $this->__soapCall('SviPodaciOTipovimaNazivaKomore', array($parameters));
    }

    /**
     * @param SviPodaciOStatusimaStecajneMase $parameters
     * @return SviPodaciOStatusimaStecajneMaseResponse
     */
    public function SviPodaciOStatusimaStecajneMase(SviPodaciOStatusimaStecajneMase $parameters)
    {
      return $this->__soapCall('SviPodaciOStatusimaStecajneMase', array($parameters));
    }

    /**
     * @param SviPodaciOTipovimaOsnivaca $parameters
     * @return SviPodaciOTipovimaOsnivacaResponse
     */
    public function SviPodaciOTipovimaOsnivaca(SviPodaciOTipovimaOsnivaca $parameters)
    {
      return $this->__soapCall('SviPodaciOTipovimaOsnivaca', array($parameters));
    }

    /**
     * @param SviPodaciOStatusimaSportskogUdruzenja $parameters
     * @return SviPodaciOStatusimaSportskogUdruzenjaResponse
     */
    public function SviPodaciOStatusimaSportskogUdruzenja(SviPodaciOStatusimaSportskogUdruzenja $parameters)
    {
      return $this->__soapCall('SviPodaciOStatusimaSportskogUdruzenja', array($parameters));
    }

    /**
     * @param SviPodaciONacinuNastankaStecajneMase $parameters
     * @return SviPodaciONacinuNastankaStecajneMaseResponse
     */
    public function SviPodaciONacinuNastankaStecajneMase(SviPodaciONacinuNastankaStecajneMase $parameters)
    {
      return $this->__soapCall('SviPodaciONacinuNastankaStecajneMase', array($parameters));
    }

    /**
     * @param SviPodaciOVrstiZadruge $parameters
     * @return SviPodaciOVrstiZadrugeResponse
     */
    public function SviPodaciOVrstiZadruge(SviPodaciOVrstiZadruge $parameters)
    {
      return $this->__soapCall('SviPodaciOVrstiZadruge', array($parameters));
    }

    /**
     * @param SviPodaciODiscipliniSporta $parameters
     * @return SviPodaciODiscipliniSportaReponse
     */
    public function SviPodaciODiscipliniSporta(SviPodaciODiscipliniSporta $parameters)
    {
      return $this->__soapCall('SviPodaciODiscipliniSporta', array($parameters));
    }

    /**
     * @param SviPodaciOOblastiSporta $parameters
     * @return SviPodaciOOblastiSportaReponse
     */
    public function SviPodaciOOblastiSporta(SviPodaciOOblastiSporta $parameters)
    {
      return $this->__soapCall('SviPodaciOOblastiSporta', array($parameters));
    }

    /**
     * @param SviPodaciORazloguBrisanja $parameters
     * @return SviPodaciORazloguBrisanjaReponse
     */
    public function SviPodaciORazloguBrisanja(SviPodaciORazloguBrisanja $parameters)
    {
      return $this->__soapCall('SviPodaciORazloguBrisanja', array($parameters));
    }

}
