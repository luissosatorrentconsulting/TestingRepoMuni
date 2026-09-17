<div>
    <div class="flex justify-end mb-2">
        <a
            href="{{ $url }}"
            target="_blank"
            class="text-sm font-medium text-primary-600 hover:underline dark:text-primary-400"
        >
            Abrir en pestaña nueva ↗
        </a>
    </div>

    <iframe
        src="{{ $url }}"
        style="width: 100%; height: 75vh; border: 0; border-radius: 0.5rem;"
        class="bg-white"
    ></iframe>
</div>
