<?php

namespace App\PL\Test;

class PlService extends \SoapClient
{

    /**
     * @var array $classmap The defined classes
     */
    private static $classmap = array (
      'PreuzmiPodatkeOPrivrednomSubjektu' => 'App\\PL\\Test\\PreuzmiPodatkeOPrivrednomSubjektu',
      'PrivredniSubjektiUlazniPodaci' => 'App\\PL\\Test\\PrivredniSubjektiUlazniPodaci',
      'PrivredniSubjekatMaticniBroj' => 'App\\PL\\Test\\PrivredniSubjekatMaticniBroj',
      'PrivredniSubjekatGrupaPodataka' => 'App\\PL\\Test\\PrivredniSubjekatGrupaPodataka',
      'PreuzmiPodatkeOPrivrednomSubjektuResponse' => 'App\\PL\\Test\\PreuzmiPodatkeOPrivrednomSubjektuResponse',
      'ArrayOfPrivredniSubjekat' => 'App\\PL\\Test\\ArrayOfPrivredniSubjekat',
      'PrivredniSubjekat' => 'App\\PL\\Test\\PrivredniSubjekat',
      'Grupa' => 'App\\PL\\Test\\Grupa',
      'Podatak' => 'App\\PL\\Test\\Podatak',
      'PreuzmiPromenePodatakaOPrivrednimSubjektima' => 'App\\PL\\Test\\PreuzmiPromenePodatakaOPrivrednimSubjektima',
      'PrivredniSubjektiPromenePoDatumu' => 'App\\PL\\Test\\PrivredniSubjektiPromenePoDatumu',
      'PreuzmiPromenePodatakaOPrivrednimSubjektimaResponse' => 'App\\PL\\Test\\PreuzmiPromenePodatakaOPrivrednimSubjektimaResponse',
      'SviPodaciODelatnostima' => 'App\\PL\\Test\\SviPodaciODelatnostima',
      'SviPodaciODelatnostimaResponse' => 'App\\PL\\Test\\SviPodaciODelatnostimaResponse',
      'Sifarnik' => 'App\\PL\\Test\\Sifarnik',
      'Zapis' => 'App\\PL\\Test\\Zapis',
      'SviPodaciODrzavama' => 'App\\PL\\Test\\SviPodaciODrzavama',
      'SviPodaciODrzavamaResponse' => 'App\\PL\\Test\\SviPodaciODrzavamaResponse',
      'SviPodaciOFunkcijamaOsnivacaZaduzbineIliFondacije' => 'App\\PL\\Test\\SviPodaciOFunkcijamaOsnivacaZaduzbineIliFondacije',
      'SviPodaciOFunkcijamaOsnivacaZaduzbineIliFondacijeResponse' => 'App\\PL\\Test\\SviPodaciOFunkcijamaOsnivacaZaduzbineIliFondacijeResponse',
      'SviPodaciOFunkcijamaZakonskogZastupnikaPrivrednogDrustva' => 'App\\PL\\Test\\SviPodaciOFunkcijamaZakonskogZastupnikaPrivrednogDrustva',
      'SviPodaciOFunkcijamaZakonskogZastupnikaPrivrednogDrustvaResponse' => 'App\\PL\\Test\\SviPodaciOFunkcijamaZakonskogZastupnikaPrivrednogDrustvaResponse',
      'SviPodaciOGrupamaDelatnosti' => 'App\\PL\\Test\\SviPodaciOGrupamaDelatnosti',
      'SviPodaciOGrupamaDelatnostiResponse' => 'App\\PL\\Test\\SviPodaciOGrupamaDelatnostiResponse',
      'SviPodaciOMestima' => 'App\\PL\\Test\\SviPodaciOMestima',
      'SviPodaciOMestimaResponse' => 'App\\PL\\Test\\SviPodaciOMestimaResponse',
      'SviPodaciOOblastimaDelatnosti' => 'App\\PL\\Test\\SviPodaciOOblastimaDelatnosti',
      'SviPodaciOOblastimaDelatnostiResponse' => 'App\\PL\\Test\\SviPodaciOOblastimaDelatnostiResponse',
      'SviPodaciOOblicimaOrganizovanjaZaduzbineIliFondacije' => 'App\\PL\\Test\\SviPodaciOOblicimaOrganizovanjaZaduzbineIliFondacije',
      'SviPodaciOOblicimaOrganizovanjaZaduzbineIliFondacijeResponse' => 'App\\PL\\Test\\SviPodaciOOblicimaOrganizovanjaZaduzbineIliFondacijeResponse',
      'SviPodaciOOdsecimaDelatnosti' => 'App\\PL\\Test\\SviPodaciOOdsecimaDelatnosti',
      'SviPodaciOOdsecimaDelatnostiResponse' => 'App\\PL\\Test\\SviPodaciOOdsecimaDelatnostiResponse',
      'SviPodaciOOkruzima' => 'App\\PL\\Test\\SviPodaciOOkruzima',
      'SviPodaciOOkruzimaResponse' => 'App\\PL\\Test\\SviPodaciOOkruzimaResponse',
      'SviPodaciOOpstinama' => 'App\\PL\\Test\\SviPodaciOOpstinama',
      'SviPodaciOOpstinamaResponse' => 'App\\PL\\Test\\SviPodaciOOpstinamaResponse',
      'SviPodaciOPravnimFormama' => 'App\\PL\\Test\\SviPodaciOPravnimFormama',
      'SviPodaciOPravnimFormamaResponse' => 'App\\PL\\Test\\SviPodaciOPravnimFormamaResponse',
      'SviPodaciOSektorimaDelatnosti' => 'App\\PL\\Test\\SviPodaciOSektorimaDelatnosti',
      'SviPodaciOSektorimaDelatnostiResponse' => 'App\\PL\\Test\\SviPodaciOSektorimaDelatnostiResponse',
      'SviPodaciOStatusimaPreduzetnika' => 'App\\PL\\Test\\SviPodaciOStatusimaPreduzetnika',
      'SviPodaciOStatusimaPreduzetnikaResponse' => 'App\\PL\\Test\\SviPodaciOStatusimaPreduzetnikaResponse',
      'SviPodaciOStatusimaPrivrednogDrustva' => 'App\\PL\\Test\\SviPodaciOStatusimaPrivrednogDrustva',
      'SviPodaciOStatusimaPrivrednogDrustvaResponse' => 'App\\PL\\Test\\SviPodaciOStatusimaPrivrednogDrustvaResponse',
      'SviPodaciOStatusimaUdruzenja' => 'App\\PL\\Test\\SviPodaciOStatusimaUdruzenja',
      'SviPodaciOStatusimaUdruzenjaResponse' => 'App\\PL\\Test\\SviPodaciOStatusimaUdruzenjaResponse',
      'SviPodaciOStatusimaZaduzbineIliFondacije' => 'App\\PL\\Test\\SviPodaciOStatusimaZaduzbineIliFondacije',
      'SviPodaciOStatusimaZaduzbineIliFondacijeResponse' => 'App\\PL\\Test\\SviPodaciOStatusimaZaduzbineIliFondacijeResponse',
      'SviPodaciOTipoviimaOblastiOstvarivanjaCiljevaUdruzenja' => 'App\\PL\\Test\\SviPodaciOTipoviimaOblastiOstvarivanjaCiljevaUdruzenja',
      'SviPodaciOTipoviimaOblastiOstvarivanjaCiljevaUdruzenjaResponse' => 'App\\PL\\Test\\SviPodaciOTipoviimaOblastiOstvarivanjaCiljevaUdruzenjaResponse',
      'SviPodaciOTipoviimaZabeleskiUdruzenja' => 'App\\PL\\Test\\SviPodaciOTipoviimaZabeleskiUdruzenja',
      'SviPodaciOTipoviimaZabeleskiUdruzenjaResponse' => 'App\\PL\\Test\\SviPodaciOTipoviimaZabeleskiUdruzenjaResponse',
      'SviPodaciOTipovimaCiljevaZaduzbineIliFondacije' => 'App\\PL\\Test\\SviPodaciOTipovimaCiljevaZaduzbineIliFondacije',
      'SviPodaciOTipovimaCiljevaZaduzbineIliFondacijeResponse' => 'App\\PL\\Test\\SviPodaciOTipovimaCiljevaZaduzbineIliFondacijeResponse',
      'SviPodaciOTipovimaClanaPrivrednogDrustva' => 'App\\PL\\Test\\SviPodaciOTipovimaClanaPrivrednogDrustva',
      'SviPodaciOTipovimaClanaPrivrednogDrustvaResponse' => 'App\\PL\\Test\\SviPodaciOTipovimaClanaPrivrednogDrustvaResponse',
      'SviPodaciOTipovimaFunkcijeZastupnikaZaduzbineIliFondacije' => 'App\\PL\\Test\\SviPodaciOTipovimaFunkcijeZastupnikaZaduzbineIliFondacije',
      'SviPodaciOTipovimaFunkcijeZastupnikaZaduzbineIliFondacijeResponse' => 'App\\PL\\Test\\SviPodaciOTipovimaFunkcijeZastupnikaZaduzbineIliFondacijeResponse',
      'SviPodaciOTipovimaJezika' => 'App\\PL\\Test\\SviPodaciOTipovimaJezika',
      'SviPodaciOTipovimaJezikaResponse' => 'App\\PL\\Test\\SviPodaciOTipovimaJezikaResponse',
      'SviPodaciOTipovimaKapitalaPrivrednogDrustva' => 'App\\PL\\Test\\SviPodaciOTipovimaKapitalaPrivrednogDrustva',
      'SviPodaciOTipovimaKapitalaPrivrednogDrustvaResponse' => 'App\\PL\\Test\\SviPodaciOTipovimaKapitalaPrivrednogDrustvaResponse',
      'SviPodaciOTipovimaKapitalaZaduzbineIliFondacije' => 'App\\PL\\Test\\SviPodaciOTipovimaKapitalaZaduzbineIliFondacije',
      'SviPodaciOTipovimaKapitalaZaduzbineIliFondacijeResponse' => 'App\\PL\\Test\\SviPodaciOTipovimaKapitalaZaduzbineIliFondacijeResponse',
      'SviPodaciOTipovimaLica' => 'App\\PL\\Test\\SviPodaciOTipovimaLica',
      'SviPodaciOTipovimaLicaResponse' => 'App\\PL\\Test\\SviPodaciOTipovimaLicaResponse',
      'SviPodaciOTipovimaNacinaPromene' => 'App\\PL\\Test\\SviPodaciOTipovimaNacinaPromene',
      'SviPodaciOTipovimaNacinaPromeneResponse' => 'App\\PL\\Test\\SviPodaciOTipovimaNacinaPromeneResponse',
      'SviPodaciOTipovimaNazivaUdruzenja' => 'App\\PL\\Test\\SviPodaciOTipovimaNazivaUdruzenja',
      'SviPodaciOTipovimaNazivaUdruzenjaResponse' => 'App\\PL\\Test\\SviPodaciOTipovimaNazivaUdruzenjaResponse',
      'SviPodaciOTipovimaPoslovnogSubjekta' => 'App\\PL\\Test\\SviPodaciOTipovimaPoslovnogSubjekta',
      'SviPodaciOTipovimaPoslovnogSubjektaResponse' => 'App\\PL\\Test\\SviPodaciOTipovimaPoslovnogSubjektaResponse',
      'SviPodaciOTipovimaRegistracionogPostupka' => 'App\\PL\\Test\\SviPodaciOTipovimaRegistracionogPostupka',
      'SviPodaciOTipovimaRegistracionogPostupkaResponse' => 'App\\PL\\Test\\SviPodaciOTipovimaRegistracionogPostupkaResponse',
      'SviPodaciOTipovimaZabeleskiZaduzbineIliFondacije' => 'App\\PL\\Test\\SviPodaciOTipovimaZabeleskiZaduzbineIliFondacije',
      'SviPodaciOTipovimaZabeleskiZaduzbineIliFondacijeResponse' => 'App\\PL\\Test\\SviPodaciOTipovimaZabeleskiZaduzbineIliFondacijeResponse',
      'SviPodaciOVelicinamaPoslovnogSubjekta' => 'App\\PL\\Test\\SviPodaciOVelicinamaPoslovnogSubjekta',
      'SviPodaciOVelicinamaPoslovnogSubjektaResponse' => 'App\\PL\\Test\\SviPodaciOVelicinamaPoslovnogSubjektaResponse',
      'SviPodaciOVrstamaZabelezbePreduzetnika' => 'App\\PL\\Test\\SviPodaciOVrstamaZabelezbePreduzetnika',
      'SviPodaciOVrstamaZabelezbePreduzetnikaResponse' => 'App\\PL\\Test\\SviPodaciOVrstamaZabelezbePreduzetnikaResponse',
      'SviPodaciOVrstamaZabelezbePrivrednogDrustva' => 'App\\PL\\Test\\SviPodaciOVrstamaZabelezbePrivrednogDrustva',
      'SviPodaciOVrstamaZabelezbePrivrednogDrustvaResponse' => 'App\\PL\\Test\\SviPodaciOVrstamaZabelezbePrivrednogDrustvaResponse',
      'SviPodaciOVrstamaZabelezbeSportskogUdruzenja' => 'App\\PL\\Test\\SviPodaciOVrstamaZabelezbeSportskogUdruzenja',
      'SviPodaciOVrstamaZabelezbeSportskogUdruzenjaResponse' => 'App\\PL\\Test\\SviPodaciOVrstamaZabelezbeSportskogUdruzenjaResponse',
      'SviPodaciOVrstamaOblikaOrganizovanjaSportskogUdruzenja' => 'App\\PL\\Test\\SviPodaciOVrstamaOblikaOrganizovanjaSportskogUdruzenja',
      'SviPodaciOVrstamaOblikaOrganizovanjaSportskogUdruzenjaResponse' => 'App\\PL\\Test\\SviPodaciOVrstamaOblikaOrganizovanjaSportskogUdruzenjaResponse',
      'SviPodaciOVrstamaGraneSporta' => 'App\\PL\\Test\\SviPodaciOVrstamaGraneSporta',
      'SviPodaciOVrstamaGraneSportaResponse' => 'App\\PL\\Test\\SviPodaciOVrstamaGraneSportaResponse',
      'SviPodaciOOblicimaOrganizovanjaUdruzenja' => 'App\\PL\\Test\\SviPodaciOOblicimaOrganizovanjaUdruzenja',
      'SviPodaciOOblicimaOrganizovanjaUdruzenjaResponse' => 'App\\PL\\Test\\SviPodaciOOblicimaOrganizovanjaUdruzenjaResponse',
      'SviPodaciOStatusimaKomore' => 'App\\PL\\Test\\SviPodaciOStatusimaKomore',
      'SviPodaciOStatusimaKomoreResponse' => 'App\\PL\\Test\\SviPodaciOStatusimaKomoreResponse',
      'SviPodaciOTipovimaNazivaKomore' => 'App\\PL\\Test\\SviPodaciOTipovimaNazivaKomore',
      'SviPodaciOTipovimaNazivaKomoreResponse' => 'App\\PL\\Test\\SviPodaciOTipovimaNazivaKomoreResponse',
      'SviPodaciOStatusimaStecajneMase' => 'App\\PL\\Test\\SviPodaciOStatusimaStecajneMase',
      'SviPodaciOStatusimaStecajneMaseResponse' => 'App\\PL\\Test\\SviPodaciOStatusimaStecajneMaseResponse',
      'SviPodaciOTipovimaOsnivaca' => 'App\\PL\\Test\\SviPodaciOTipovimaOsnivaca',
      'SviPodaciOTipovimaOsnivacaResponse' => 'App\\PL\\Test\\SviPodaciOTipovimaOsnivacaResponse',
      'SviPodaciOStatusimaSportskogUdruzenja' => 'App\\PL\\Test\\SviPodaciOStatusimaSportskogUdruzenja',
      'SviPodaciOStatusimaSportskogUdruzenjaResponse' => 'App\\PL\\Test\\SviPodaciOStatusimaSportskogUdruzenjaResponse',
      'SviPodaciONacinuNastankaStecajneMase' => 'App\\PL\\Test\\SviPodaciONacinuNastankaStecajneMase',
      'SviPodaciONacinuNastankaStecajneMaseResponse' => 'App\\PL\\Test\\SviPodaciONacinuNastankaStecajneMaseResponse',
      'SviPodaciOVrstiZadruge' => 'App\\PL\\Test\\SviPodaciOVrstiZadruge',
      'SviPodaciOVrstiZadrugeResponse' => 'App\\PL\\Test\\SviPodaciOVrstiZadrugeResponse',
      'SviPodaciODiscipliniSporta' => 'App\\PL\\Test\\SviPodaciODiscipliniSporta',
      'SviPodaciODiscipliniSportaResponse' => 'App\\PL\\Test\\SviPodaciODiscipliniSportaResponse',
      'SviPodaciOOblastiSporta' => 'App\\PL\\Test\\SviPodaciOOblastiSporta',
      'SviPodaciOOblastiSportaResponse' => 'App\\PL\\Test\\SviPodaciOOblastiSportaResponse',
      'SviPodaciORazloguBrisanja' => 'App\\PL\\Test\\SviPodaciORazloguBrisanja',
      'SviPodaciORazloguBrisanjaResponse' => 'App\\PL\\Test\\SviPodaciORazloguBrisanjaResponse',
    );

    /**
     * @param array $options A array of config values
     * @param string $wsdl The wsdl file to use
     */
    public function __construct(array $options = array(), $wsdl = null)
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
        $wsdl = 'https://service1.apr.gov.rs:4430/plwstest/PlService.svc?wsdl';
      }
      $username = 'sbrs.test';
      $password = 'm2J(2X';
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
