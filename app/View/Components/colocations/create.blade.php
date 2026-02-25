<form method="POST" action="{{ route('colocations.store') }}">
    @csrf

    <input type="text" name="name" placeholder="Nom colocation" required>

    <textarea name="description" placeholder="Description"></textarea>

    <button type="submit">Créer</button>
</form>