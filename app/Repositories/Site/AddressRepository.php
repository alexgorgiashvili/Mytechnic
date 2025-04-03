<?php

namespace App\Repositories\Site;

use App\Models\Address;
use App\Models\City;
use App\Models\Country;
use App\Models\State;
use App\Repositories\Interfaces\Site\AddressInterface;
use Illuminate\Support\Str;
use Sentinel;

class AddressRepository implements AddressInterface {

    //for APi
    public function userAddress()
    {
        return [
            'shipping_addresses' => Address::where('user_id',authId())->where('type',0)->get(),
            'billing_addresses' => Address::where('user_id',authId())->where('type',1)->get(),
        ];
    }

    // public function storeAddress($request)
    // {
    //     // If the user is logged in, use their ID, otherwise leave the user_id empty
    //     if (authUser()) {
    //         $request['user_id'] = authId();
    //     } else {
    //         $request['user_id'] = null; // No user ID for guest users
    //     }
    
    //     // Assign default country and state IDs
    //     $request['country_id'] = 81;
    //     $request['state_id'] = 1;
    
    //     // Retrieve city, state, and country information based on IDs
    //     $country = Country::find($request['country_id']);
    //     $state = State::find($request['state_id']);
    //     $city = City::find($request['city_id']);
    
    //     // Assign address IDs
    //     $request['address_ids'] = [
    //         'country_id' => $request['country_id'],
    //         'state_id' => $request['state_id'],
    //         'city_id' => $request['city_id'] ?? '',
    //     ];
    
    //     // Set the names of country, state, and city
    //     $request['country'] = $country->name;
    //     $request['state'] = $state->name;
    
    //     if ($request['city_id']) {
    //         $request['city'] = $city->name;
    //         $request['longitude'] = $city->longitude;
    //         $request['latitude'] = $city->latitude;
    //     }
    
    //     // For logged-in users, handle default shipping and billing addresses
    //     if (authUser()) {
    //         // Set default shipping and billing if the user doesn't have any addresses yet
    //         if (blank(Sentinel::getUser()->addresses)) {
    //             $request['default_shipping'] = 1;
    //             $request['default_billing'] = 1;
    //         }
    
    //         // If an address ID is provided, update the existing address
    //         if (!empty($request['id'])) {
    //             $address = Address::find($request['id']);
    //             $address->update($request);
    //         } else {
    //             // Create a new address for the user
    //             $address = Address::create($request);
    //         }
    //     } else {
    //         // Handle guest user address creation (e.g., by assigning to a session or temporary user)
    //         // Here, you can store the address as a temporary address or save it without linking to a user
    //         $address = Address::create($request);
    //     }
    
    //     return true;
    // }
    public function storeAddress($request)
{
    if (authUser()) {
        $request['user_id'] = authId();
    } else {
        $request['user_id'] = null; // No user ID for guest users
    }

    $request['country_id'] = 81;
    $request['state_id'] = 1;

    $country = Country::find($request['country_id']);
    $state = State::find($request['state_id']);
    $city = City::find($request['city_id']);

    $request['address_ids'] = [
        'country_id' => $request['country_id'],
        'state_id' => $request['state_id'],
        'city_id' => $request['city_id'] ?? '',
    ];

    $request['country'] = $country->name;
    $request['state'] = $state->name;

    if ($request['city_id']) {
        $request['city'] = $city->name;
        $request['longitude'] = $city->longitude;
        $request['latitude'] = $city->latitude;
    }

    if (authUser()) {
        if (blank(Sentinel::getUser()->addresses)) {
            $request['default_shipping'] = 1;
            $request['default_billing'] = 1;
        }

        if (!empty($request['id'])) {
            $address = Address::find($request['id']);
            $address->update($request);
        } else {
            $address = Address::create($request);
        }
    } else {
        $address = Address::create($request);
    }

    return $address; // Return the saved address object
}


    public function makeDefault($type, $id)
    {
        if ($type == 'shipping'):
            Sentinel::getUser()->addresses()->update(['default_shipping' => 0]);
            Sentinel::getUser()->addresses()->where('id',$id)->update(['default_shipping' => 1]);
        elseif ($type == 'billing'):
            Sentinel::getUser()->addresses()->update(['default_billing' => 0]);
            Sentinel::getUser()->addresses()->where('id',$id)->update(['default_billing' => 1]);
        endif;

    }

    public function deleteAddress($id): int
    {
        if (authUser())
        {
            return Address::destroy($id);
        }
        else{
            $addresses = session()->get('addresses');

            foreach ($addresses as $key => $cart) {
                if (array_key_exists('id',$cart) && $cart['id'] == $id) {
                    unset($addresses[$key]);
                }
            }
            session()->put('addresses', $addresses);
            return true;
        }
    }

    public function store($request)
    {
        $country = Country::find($request['country_id']);
        $state   = State::find($request['state_id']);
        $city    = City::find($request['city_id']);

        $request['address_ids'] = [
            'country_id'    => $request['country_id'],
            'state_id'      => $request['state_id'],
            'city_id'       => $request['city_id'],
        ];

        $request['country'] = $country->name;
        $request['state']   = $state->name;
        $request['city']    = $city->name;
        $request['longitude']   = $city->longitude;
        $request['latitude']    = $city->latitude;

        return Address::create($request);
    }

    public function edit($id)
    {
        return Address::find($id);
    }

    public function update($request,$id)
    {
        $country = Country::find($request['country_id']);
        $state   = State::find($request['state_id']);
        $city    = City::find($request['city_id']);

        $request['address_ids'] = [
            'country_id'    => $request['country_id'],
            'state_id'      => $request['state_id'],
            'city_id'       => $request['city_id'],
        ];

        $request['country'] = $country->name;
        $request['state']   = $state->name;
        $request['city']    = $city->name;
        $request['longitude']   = $city->longitude;
        $request['latitude']    = $city->latitude;

        $address = Address::find($id);

        $address->update($request);

        return $address;
    }

    public function destroy($id): int
    {
        return Address::destroy($id);
    }

    public function getAdrByUser($id)
    {
        return Address::where('user_id',$id)->get();
    }
}
