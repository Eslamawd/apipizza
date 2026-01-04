<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Stichoza\GoogleTranslate\GoogleTranslate;

class CategoryController extends Controller
{
    public function store(Request $request)
    {
        // ✅ تصحيح أخطاء الـ validation
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'menu_id' => 'required|exists:menus,id',
        ]);

        // ✅ ترجمة الاسم تلقائيًا إلى الإنجليزية
        $translator = new GoogleTranslate('en');
        $translatedName = $translator->translate($validated['name']);

        // ✅ إنشاء التصنيف مع الاسم المترجم
        $category = Category::create([
            'name' => $validated['name'],
            'name_en' => $translatedName,
            'menu_id' => $validated['menu_id'],
        ]);

        return response()->json($category, 201);
    }

    public function show(Category $category)
    {
        return response()->json($category->load('items.options'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'menu_id' => 'sometimes|exists:menus,id',
        ]);

        if (isset($validated['name'])) {
            $translator = new GoogleTranslate('en');
            $validated['name_en'] = $translator->translate($validated['name']);
        }

        $category->update($validated);

        return response()->json($category);
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return response()->json(null, 204);
    }
}
