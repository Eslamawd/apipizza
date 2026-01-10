<?php

namespace App\Http\Controllers;

use App\Models\ItemOption;
use Illuminate\Http\Request;
use Stichoza\GoogleTranslate\GoogleTranslate;

class ItemOptionController extends Controller
{
    protected $translator;

    public function __construct()
    {
        $this->translator = new GoogleTranslate('en');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'option_type' => 'required|string',
            'half' => 'sometimes|boolean',
            'item_id' => 'required|exists:items,id',
        ]);

        try {
            $data['name_en'] = $this->translator->translate($data['name']);
        } catch (\Exception $e) {
            $data['name_en'] = $data['name'];
        }

        $itemOption = ItemOption::create($data);
        return response()->json($itemOption, 201);
    }

    public function show(ItemOption $itemOption)
    {
        return response()->json($itemOption);
    }

    public function update(Request $request, ItemOption $itemOption)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'price' => 'sometimes|numeric',
            'half' => 'sometimes|boolean',
            'option_type' => 'sometimes|string',
        ]);

        if (empty($validated)) {
            return response()->json(['message' => 'No data provided to update.'], 400);
        }

        if (isset($validated['name'])) {
            try {
                $validated['name_en'] = $this->translator->translate($validated['name']);
            } catch (\Exception $e) {
                $validated['name_en'] = $validated['name'];
            }
        }

        $itemOption->update($validated);
        return response()->json($itemOption);
    }

    public function destroy(ItemOption $itemOption)
    {
        $itemOption->delete();
        return response()->json(null, 204);
    }
}
