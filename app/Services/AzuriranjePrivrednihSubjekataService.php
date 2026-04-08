<?php

namespace App\Services;

use App\Http\Resources\PL\PrivredniSubjekatResource;
use App\Services\PLServiceClient;
use Illuminate\Http\Request;
use App\Models\Captain;

class AzuriranjePrivrednihSubjekataService
{
    private $plServiceClient;

    public function __construct(PLServiceClient $plServiceClient)
    {
        $this->plServiceClient = $plServiceClient;
    }
    
    public function updateSingle($captainId)
    {
        $fileName = 'logs-' . date('Y-m-d H:i:s') . '.csv';
        $directory = storage_path('app/public/api-azuriranje-logs-single');
        
        if (!is_dir($directory)) 
        {
            mkdir($directory, 0755, true);
        }
        
        $filePath = $directory . '/' . $fileName;
        $file = fopen($filePath, 'w');
        
        if ($file === false) 
        {
            throw new \Exception("Cannot open file: " . $filePath);
        }

        fputcsv($file, ['captain_id', 'type', 'identification_number']);
        
        $captain = Captain::findOrFail($captainId);

		$companyType = 1;
		
		$companyTypeName = $captain->company_type ?? $captain->companyType?->company_type_name;
		
		$ids = [];

		if($companyTypeName == 'Preduzetnici') 
        {
			$companyType = 2;
		}
		else if($companyTypeName == 'Udruženje građana i sportske organizacije') 
        {
			$companyType = 3;
		}
        
        if($captain->billing_identification_number) 
        {
            $data = null;

            if(isset($ids[$captain->billing_identification_number])) 
            {
                $data = $ids[$captain->billing_identification_number];
            }
            else 
            {
                $resources = null;
                try {
                    $privredniSubjekti = $this->plServiceClient->PreuzmiPodatkeOPrivrednomSubjektu($captain->billing_identification_number, $companyType);
                    $resources = PrivredniSubjekatResource::collection($privredniSubjekti);
                } catch(\Exception $e) {
    				fputcsv($file, [ $captain->id, 'billing', $captain->billing_identification_number ]);
                }
                    
                if(isset($resources)) 
                {
                    $data = $this->formatData($resources);
                    $ids[$captain->billing_identification_number] = $data;
                }
                else 
                {
                    $ids[$captain->billing_identification_number] = [ 'error' => true ];
                    $data = $ids[$captain->billing_identification_number];
                }
            }
                
            if(!isset($data['error'])) 
            {
                $updated = false;

                if($data['poslovnoIme'] && $captain->billing_company != $data['poslovnoIme']) 
                {
                    $captain->billing_company = $data['poslovnoIme'];
                    $updated = true;
                }

                if($data['pib'] && $captain->billing_pin != $data['pib']) 
                {
                    $captain->billing_pin = $data['pib'];
                    $updated = true;
                }

                if($data['adresaUlica'] && $data['adresaBroj'] && $captain->billing_address != $data['adresaUlica'].' '.$data['adresaBroj']) 
                {
                    $captain->billing_address = $data['adresaUlica'].' '.$data['adresaBroj'];
                    $updated = true;
                }

                if($data['adresaMjesto'] && $captain->billing_city != $data['adresaMjesto']) 
                {
                    $captain->billing_city = $data['adresaMjesto'];
                    $updated = true;
                }

                if($data['adresaPosta'] && $captain->billing_postcode != $data['adresaPosta']) 
                {
                    $captain->billing_postcode = $data['adresaPosta'];
                    $updated = true;
                }

                if($updated) $captain->save();
            }
        }
		
	    foreach($captain->captainAddresses as $address) 
        {
	        if($address->identification_number) 
            {
                $data = null;

	            if(isset($ids[$address->identification_number])) 
                {
		    	    $data = $ids[$address->identification_number];
                }
                else 
                {
                    $resources = null;
                    try {
                        $privredniSubjekti = $this->plServiceClient->PreuzmiPodatkeOPrivrednomSubjektu($address->identification_number, $companyType);
                        $resources = PrivredniSubjekatResource::collection($privredniSubjekti);
                    } catch(\Exception $e) {
    					fputcsv($file, [ $captain->id, 'address', $address->identification_number ]);
                    }
                        
                    if(isset($resources)) 
                    {
                        $data = $this->formatData($resources);
                        $ids[$address->identification_number] = $data;
                    }
                    else 
                    {
                        $ids[$address->identification_number] = [ 'error' => true ];
			    	    $data = $ids[$address->identification_number];
                    }
                }

                if(!isset($data['error'])) 
                {
                    $updated = false;

                    if($data['poslovnoIme'] && $address->company_name != $data['poslovnoIme']) 
                    {
                        $address->company_name = $data['poslovnoIme'];
                        $updated = true;
                    }

                    if($data['pib'] && $address->pin != $data['pib']) 
                    {
                        $address->pin = $data['pib'];
                        $updated = true;
                    }

                    if($data['adresaUlica'] && $data['adresaBroj'] && $address->address != $data['adresaUlica'].' '.$data['adresaBroj']) 
                    {
                        $address->address = $data['adresaUlica'].' '.$data['adresaBroj'];
                        $updated = true;
                    }

                    if($data['adresaMjesto'] && $address->city != $data['adresaMjesto']) 
                    {
                        $address->city = $data['adresaMjesto'];
                        $updated = true;
                    }

                    if($data['adresaPosta'] && $address->postal_code != $data['adresaPosta']) 
                    {
                        $address->postal_code = $data['adresaPosta'];
                        $updated = true;
                    }

                    if($updated) $address->save();
                }
	        }
        }
        
        fclose($file);
    }

    public function update()
    {
        $captains = Captain::where('organizer_id', 2)
            ->with('captainAddresses')
            ->get();
            
        $fileName = 'logs-' . date('Y-m-d') . '.csv';
        $directory = storage_path('app/public/api-azuriranje-logs');
        
        if (!is_dir($directory)) 
        {
            mkdir($directory, 0755, true);
        }
        
        $filePath = $directory . '/' . $fileName;
        $file = fopen($filePath, 'w');
        
        if ($file === false) 
        {
            throw new \Exception("Cannot open file: " . $filePath);
        }

        fputcsv($file, ['captain_id', 'type', 'identification_number']);
            
        foreach($captains as $captain) 
        {
            $ids = [];

			$companyType = 1;
			
			$companyTypeName = $captain->company_type ?? $captain->companyType?->company_type_name;

			if($companyTypeName == 'Preduzetnici') 
            {
				$companyType = 2;
			}
			else if($companyTypeName == 'Udruženje građana i sportske organizacije') 
            {
				$companyType = 3;
			}
            
            if($captain->billing_identification_number) 
            {
                $data = null;

                if(isset($ids[$captain->billing_identification_number])) 
                {
                    $data = $ids[$captain->billing_identification_number];
                }
                else 
                {
                    $resources = null;
                    try {
                        $privredniSubjekti = $this->plServiceClient->PreuzmiPodatkeOPrivrednomSubjektu($captain->billing_identification_number, $companyType);
                        $resources = PrivredniSubjekatResource::collection($privredniSubjekti);
                    } catch(\Exception $e) {
        				fputcsv($file, [ $captain->id, 'billing', $captain->billing_identification_number ]);
                    }
                        
                    if(isset($resources)) 
                    {
                        $data = $this->formatData($resources);
                        $ids[$captain->billing_identification_number] = $data;
                    }
                    else 
                    {
                        $ids[$captain->billing_identification_number] = [ 'error' => true ];
                        $data = $ids[$captain->billing_identification_number];
                    }
                }
                    
                if(!isset($data['error'])) 
                {
                    $updated = false;

                    if($data['poslovnoIme'] && $captain->billing_company != $data['poslovnoIme']) 
                    {
                        $captain->billing_company = $data['poslovnoIme'];
                        $updated = true;
                    }

                    if($data['pib'] && $captain->billing_pin != $data['pib']) 
                    {
                        $captain->billing_pin = $data['pib'];
                        $updated = true;
                    }

                    if($data['adresaUlica'] && $data['adresaBroj'] && $captain->billing_address != $data['adresaUlica'].' '.$data['adresaBroj']) 
                    {
                        $captain->billing_address = $data['adresaUlica'].' '.$data['adresaBroj'];
                        $updated = true;
                    }

                    if($data['adresaMjesto'] && $captain->billing_city != $data['adresaMjesto']) 
                    {
                        $captain->billing_city = $data['adresaMjesto'];
                        $updated = true;
                    }

                    if($data['adresaPosta'] && $captain->billing_postcode != $data['adresaPosta']) 
                    {
                        $captain->billing_postcode = $data['adresaPosta'];
                        $updated = true;
                    }

                    if($updated) $captain->save();
                }
            }
			
		    foreach($captain->captainAddresses as $address) 
            {
		        if($address->identification_number) 
                {
                    $data = null;

		            if(isset($ids[$address->identification_number])) 
                    {
			    	    $data = $ids[$address->identification_number];
                    }
                    else 
                    {
                        $resources = null;
                        try {
                            $privredniSubjekti = $this->plServiceClient->PreuzmiPodatkeOPrivrednomSubjektu($address->identification_number, $companyType);
                            $resources = PrivredniSubjekatResource::collection($privredniSubjekti);
                        } catch(\Exception $e) {
        					fputcsv($file, [ $captain->id, 'address', $address->identification_number ]);
                        }
                            
                        if(isset($resources)) 
                        {
                            $data = $this->formatData($resources);
                            $ids[$address->identification_number] = $data;
                        }
                        else 
                        {
                            $ids[$address->identification_number] = [ 'error' => true ];
				    	    $data = $ids[$address->identification_number];
                        }
                    }

                    if(!isset($data['error'])) 
                    {
                        $updated = false;

                        if($data['poslovnoIme'] && $address->company_name != $data['poslovnoIme']) 
                        {
                            $address->company_name = $data['poslovnoIme'];
                            $updated = true;
                        }

                        if($data['pib'] && $address->pin != $data['pib']) 
                        {
                            $address->pin = $data['pib'];
                            $updated = true;
                        }

                        if($data['adresaUlica'] && $data['adresaBroj'] && $address->address != $data['adresaUlica'].' '.$data['adresaBroj']) 
                        {
                            $address->address = $data['adresaUlica'].' '.$data['adresaBroj'];
                            $updated = true;
                        }

                        if($data['adresaMjesto'] && $address->city != $data['adresaMjesto']) 
                        {
                            $address->city = $data['adresaMjesto'];
                            $updated = true;
                        }

                        if($data['adresaPosta'] && $address->postal_code != $data['adresaPosta']) 
                        {
                            $address->postal_code = $data['adresaPosta'];
                            $updated = true;
                        }

                        if($updated) $address->save();
                    }
		        }
            }
        }

		fclose($file);
    }

    private function formatData($resources)
	{
        $poslovnoIme = null;
        $poslovnoImeSkraceno = null;
        $pib = null;
        $adresaUlica = null;
        $adresaBroj = null;
        $adresaMjesto = null;
        $adresaPosta = null;
    
        foreach($resources as $resource) 
        {
            foreach($resource->grupa as $grupa) 
            {
                if($grupa->id == 1005) 
                { // poslovno ime
                    foreach($grupa->podatak as $podatak) 
                    {
                        if($podatak->naziv == 'PoslovnoImeLatinica') 
                        {
                            $poslovnoIme = $podatak->vrednost;
                        }
                    }
                }

                if($grupa->id == 1006) 
                { // skraceno poslovno ime
                    foreach($grupa->podatak as $podatak) 
                    {
                        if($podatak->naziv == 'SkracenoPoslovnoImeLatinica') 
                        {
                            $poslovnoImeSkraceno = $podatak->vrednost;
                        }
                    }
                }

                if($grupa->id == 1017) 
                { // PIB
                    foreach($grupa->podatak as $podatak) 
                    {
                        if($podatak->naziv == 'PIB') 
                        {
                            $pib = $podatak->vrednost;
                        }
                    }
                }

                if($grupa->id == 1011) 
                { // Adresa
                    foreach($grupa->podatak as $podatak) 
                    {
                        if($podatak->naziv == 'NazivUliceLatinica') 
                        {
                            $adresaUlica = $podatak->vrednost;
                        }

                        if($podatak->naziv == 'AdresaBroj') 
                        {
                            $adresaBroj = $podatak->vrednost;
                        }

                        if($podatak->naziv == 'NazivPoste') 
                        {
                            $adresaMjesto = $podatak->vrednost;
                        }

                        if($podatak->naziv == 'PostanskiBroj') 
                        {
                            $adresaPosta = $podatak->vrednost;
                        }
                    }
                }
            }
        }

	    return [
            'poslovnoIme' => $poslovnoImeSkraceno ?? $poslovnoIme,
            'pib' => $pib,
            'adresaUlica' => $adresaUlica,
            'adresaBroj' => $adresaBroj,
            'adresaMjesto' => $adresaMjesto,
            'adresaPosta' => $adresaPosta,
	    ];
    }
}

