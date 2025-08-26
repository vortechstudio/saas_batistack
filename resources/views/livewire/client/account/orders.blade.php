<div>
    <div class="flex flex-col gap-2 mb-10">
        <span class="font-black text-blue-800 text-4xl">Mes Commandes</span>
        <p class="text-gray-600">Consultez votre historique et suivez les commandes en cours. Accédez à vos bons de commande.</p>
    </div>
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        {{ $this->table }}
    </div>
</div>
