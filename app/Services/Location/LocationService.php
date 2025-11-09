<?php

namespace App\Services\Location;

use App\Models\Location;

class LocationService
{
    /**
     * Get all locations.
     */
    public function getAll()
    {
        return Location::latest()->paginate(10); 
    }

    /**
     * Get a single location by ID.
     */
    public function getById(int $id)
    {
        return Location::findOrFail($id);
    }

    /**
     * Create a new location.
     */
    public function create(array $data)
    {
        return Location::create($data);
    }

    /**
     * Update an existing location.
     */
    public function update(int $id, array $data)
    {
        $location = $this->getById($id);
        $location->update($data);

        return $location;
    }

    /**
     * Delete a location.
     */
    public function delete(int $id)
    {
        $location = $this->getById($id);
        $location->delete();

        return true;
    }
}