<x-app-layout title="Create New Business — AI Builds My Website">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Breadcrumbs & Header -->
        <div class="mb-6">
            <nav class="flex items-center text-xs font-medium text-slate-500 mb-2">
                <a href="{{ route('dashboard') }}" class="hover:text-slate-800">Dashboard</a>
                <span class="mx-2">/</span>
                <span class="text-slate-900 font-semibold">New Business Project</span>
            </nav>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Create New Business Profile</h1>
            <p class="text-xs text-slate-500 mt-1">
                Enter your initial business information. You can leave optional fields empty; our fact verification engine will extract additional details from uploaded documents and online sources.
            </p>
        </div>

        <form method="POST" action="{{ route('businesses.store') }}" class="space-y-6">
            @csrf

            <!-- Section 1: Basic Information -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h2 class="text-base font-bold text-slate-900">1. Basic Information</h2>
                    <p class="text-xs text-slate-500">Core identity and business category</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-xs font-semibold text-slate-700">Business Name <span class="text-rose-500">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required
                            class="mt-1 block w-full px-3 py-2 text-sm border border-slate-300 rounded-xl shadow-2xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="e.g. Urban Brew Cafe">
                    </div>

                    <div>
                        <label for="category" class="block text-xs font-semibold text-slate-700">Business Category</label>
                        <select id="category" name="category"
                            class="mt-1 block w-full px-3 py-2 text-sm border border-slate-300 rounded-xl shadow-2xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                            <option value="restaurant" {{ old('category') === 'restaurant' ? 'selected' : '' }}>Restaurant</option>
                            <option value="cafe" {{ old('category') === 'cafe' ? 'selected' : '' }}>Cafe / Coffee Shop</option>
                            <option value="salon" {{ old('category') === 'salon' ? 'selected' : '' }}>Salon & Spa</option>
                            <option value="retail" {{ old('category') === 'retail' ? 'selected' : '' }}>Retail Store</option>
                            <option value="consultant" {{ old('category') === 'consultant' ? 'selected' : '' }}>Consulting & Advisory</option>
                            <option value="fitness" {{ old('category') === 'fitness' ? 'selected' : '' }}>Fitness & Gym Centre</option>
                            <option value="professional_services" {{ old('category') === 'professional_services' ? 'selected' : '' }}>Professional Services</option>
                            <option value="other" {{ old('category') === 'other' ? 'selected' : '' }}>Other Business</option>
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="description" class="block text-xs font-semibold text-slate-700">Business Description</label>
                        <textarea id="description" name="description" rows="3"
                            class="mt-1 block w-full px-3 py-2 text-sm border border-slate-300 rounded-xl shadow-2xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Briefly describe what your business does, your story, or your primary value proposition...">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Section 2: Contact & Location -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h2 class="text-base font-bold text-slate-900">2. Contact & Location</h2>
                    <p class="text-xs text-slate-500">Public details for customers to reach you</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="phone" class="block text-xs font-semibold text-slate-700">Phone Number</label>
                        <input type="text" id="phone" name="phone" value="{{ old('phone') }}"
                            class="mt-1 block w-full px-3 py-2 text-sm border border-slate-300 rounded-xl shadow-2xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="e.g. +91 98220 12345">
                    </div>

                    <div>
                        <label for="email" class="block text-xs font-semibold text-slate-700">Business Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}"
                            class="mt-1 block w-full px-3 py-2 text-sm border border-slate-300 rounded-xl shadow-2xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="e.g. contact@urbanbrew.com">
                    </div>

                    <div class="sm:col-span-2">
                        <label for="address" class="block text-xs font-semibold text-slate-700">Complete Address</label>
                        <input type="text" id="address" name="address" value="{{ old('address') }}"
                            class="mt-1 block w-full px-3 py-2 text-sm border border-slate-300 rounded-xl shadow-2xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="e.g. Shop 4, Heritage Plaza, Station Road">
                    </div>

                    <div>
                        <label for="city" class="block text-xs font-semibold text-slate-700">City</label>
                        <input type="text" id="city" name="city" value="{{ old('city') }}"
                            class="mt-1 block w-full px-3 py-2 text-sm border border-slate-300 rounded-xl shadow-2xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="e.g. Kolhapur">
                    </div>

                    <div>
                        <label for="state" class="block text-xs font-semibold text-slate-700">State / Province</label>
                        <input type="text" id="state" name="state" value="{{ old('state') }}"
                            class="mt-1 block w-full px-3 py-2 text-sm border border-slate-300 rounded-xl shadow-2xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="e.g. Maharashtra">
                    </div>

                    <div>
                        <label for="country" class="block text-xs font-semibold text-slate-700">Country</label>
                        <input type="text" id="country" name="country" value="{{ old('country', 'India') }}"
                            class="mt-1 block w-full px-3 py-2 text-sm border border-slate-300 rounded-xl shadow-2xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
            </div>

            <!-- Section 3: Operations & Offerings -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h2 class="text-base font-bold text-slate-900">3. Operations & Offerings</h2>
                    <p class="text-xs text-slate-500">Working hours, services, and product highlights</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label for="operating_hours" class="block text-xs font-semibold text-slate-700">Operating Hours</label>
                        <input type="text" id="operating_hours" name="operating_hours" value="{{ old('operating_hours') }}"
                            class="mt-1 block w-full px-3 py-2 text-sm border border-slate-300 rounded-xl shadow-2xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="e.g. Mon - Sat: 9:00 AM - 9:00 PM, Sun: 10:00 AM - 6:00 PM">
                    </div>

                    <div>
                        <label for="services" class="block text-xs font-semibold text-slate-700">Services Offered <span class="text-slate-400 font-normal">(One per line)</span></label>
                        <textarea id="services" name="services" rows="3"
                            class="mt-1 block w-full px-3 py-2 text-sm border border-slate-300 rounded-xl shadow-2xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Specialty Espresso&#10;Cold Brew Bottling&#10;Pastry Catering">{{ old('services') }}</textarea>
                    </div>

                    <div>
                        <label for="products" class="block text-xs font-semibold text-slate-700">Products Offered <span class="text-slate-400 font-normal">(One per line)</span></label>
                        <textarea id="products" name="products" rows="3"
                            class="mt-1 block w-full px-3 py-2 text-sm border border-slate-300 rounded-xl shadow-2xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Artisan Dark Roast Beans&#10;French Press Kit&#10;Almond Croissant">{{ old('products') }}</textarea>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="additional_info" class="block text-xs font-semibold text-slate-700">Additional Information</label>
                        <textarea id="additional_info" name="additional_info" rows="2"
                            class="mt-1 block w-full px-3 py-2 text-sm border border-slate-300 rounded-xl shadow-2xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Any awards, special dietary options, parking availability, or specific instructions...">{{ old('additional_info') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Submit Button Bar -->
            <div class="flex items-center justify-end gap-3 pt-4">
                <a href="{{ route('dashboard') }}"
                    class="px-5 py-2.5 rounded-xl border border-slate-300 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                    Cancel
                </a>
                <button type="submit"
                    class="px-6 py-2.5 rounded-xl text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-xs transition-colors">
                    Save Business & Continue
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
