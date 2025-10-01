<?php

namespace Webkul\B2BSuite\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'                => $this->id,
            'first_name'        => $this->first_name,
            'last_name'         => $this->last_name,
            'gender'            => $this->gender,
            'date_of_birth'     => $this->date_of_birth,
            'email'             => $this->email,
            'type'              => $this->type,
            'phone'             => $this->phone,
            'image'             => $this->image,
            'status'            => $this->status,
            'customer_group_id' => $this->customer_group_id,
            'is_verified'       => $this->is_verified,
            'is_suspended'      => $this->is_suspended,
            'customer_role_id'  => $this->customer_role_id,
        ];
    }
}
