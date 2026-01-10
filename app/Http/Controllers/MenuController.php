<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;
use Stichoza\GoogleTranslate\GoogleTranslate;

class MenuController extends Controller
{
    public function index()
    {
        return response()->json(Menu::with('categories.items.options')->get());
    }


    public function store(Request $request)
    {

           $validated = $request->validate([
            'name' => 'required|string|max:255',
            'restaurant_id' => 'required|exists:restaurants,id',
            'image' => 'nullable|image|max:5120|mimes:jpeg,png,jpg,gif,svg',
        ]);



     if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('menu', 'public');
        }

        
        // ✅ ترجمة الاسم تلقائيًا إلى الإنجليزية
        $translator = new GoogleTranslate('en');
        $translatedName = $translator->translate($validated['name']);
        $menu = Menu::create([
            'name' => $validated['name'],
            'name_en' => $translatedName,
            'restaurant_id' => $validated['restaurant_id'],
            'image' => $validated['image'] ?? null
        ]);
        return response()->json($menu, 201);
    }

    public function show(Menu $menu)
    {
        return response()->json($menu->load('categories.items'));
    }

    public function update(Request $request, Menu $menu)
    {

  
        
           $validated = $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|max:2048|mimes:jpeg,png,jpg,gif,svg',
        ]);
        if ($request->hasFile('image')) {
            // حذف الصورة القديمة إذا كانت موجودة
            if ($menu->image && \Storage::disk('public')->exists($menu->image)) {
                \Storage::disk('public')->delete($menu->image);
            }
            
            $validated['image'] = $request->file('image')->store('menu', 'public');
            $menu->image = $validated['image'];
        }

        
        // ✅ ترجمة الاسم تلقائيًا إلى الإنجليزية
        $translator = new GoogleTranslate('en');
        $translatedName = $translator->translate($validated['name']);
        $menu->update([
            'name' => $validated['name'],
            'image' => $validated['image'],
            'name_en' => $translatedName,
        ]);
        return response()->json($menu);
    }

    public function destroy(Menu $menu)
    {
  
         if ($menu->image && \Storage::disk('public')->exists($menu->image)) {
                \Storage::disk('public')->delete($menu->image);
          }

        $menu->delete();
        return response()->json(null, 204);
    }
}
