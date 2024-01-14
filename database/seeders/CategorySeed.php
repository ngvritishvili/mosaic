<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        try {
            \DB::beginTransaction();

            $this->seedCategories();

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }

    private function seedCategories(): void
    {
        $categoriesData = [
            [
                'name' => 'none'
            ],
            [
                'name' => 'Electronics'
            ],
            [
                'name' => 'Books'
            ],
            [
                'name' => 'Nature'
            ],
            [
                'name' => 'Home'
            ],
            [
                'name' => 'Sport'
            ],
            [
                'name' => 'Clothes'
            ],
            [
                'name' => 'Boots'
            ],

        ];

        foreach ($categoriesData as $category) {
            Category::updateOrInsert(['name' => $category['name']], $category);
        }
    }
}
