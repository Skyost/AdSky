package fr.skyost.adsky.core.utils;

import java.util.*;

/**
 * A HashMap implementation that can have multiple values for the same key.
 *
 * @param <K> Type of keys.
 * @param <V> Type of values.
 */

public class ArrayMultiMap<K, V> {

	/**
	 * Inner representation.
	 */

	private final HashMap<K, List<V>> map = new HashMap<>();

	/**
	 * Puts a value to this HashMap.
	 *
	 * @param key The key.
	 * @param value The value.
	 *
	 * @return The result of map.put(key, list.add(values)).
	 */

	public final List<V> put(final K key, final V value) {
		final List<V> values = map.containsKey(key) ? map.get(key) : new ArrayList<>();
		values.add(value);
		return map.put(key, values);
	}

	/**
	 * Gets all values corresponding to the specified key.
	 *
	 * @param key The key.
	 *
	 * @return All values.
	 */

	public final List<V> get(final K key) {
		return map.get(key);
	}

	/**
	 * Removes a key from the map.
	 *
	 * @param key The key.
	 *
	 * @return The result map.remove(key).
	 */

	public final List<V> remove(final K key) {
		return map.remove(key);
	}

	/**
	 * Removes a value from the list of all values corresponding to the specified key.
	 *
	 * @param key The key.
	 * @param value The value.
	 *
	 * @return The result of map.get(key).remove(value).
	 */

	public final boolean remove(final K key, final V value) {
		final List<V> values = map.get(key);

		if(value == null) {
			return false;
		}

		final boolean result = values.remove(value);
		if(values.isEmpty()) {
			map.remove(key);
		}
		return result;
	}

	/**
	 * Gets the map size.
	 *
	 * @return The map size.
	 */

	public final int size() {
		return map.size();
	}

	/**
	 * Checks if the map is empty.
	 *
	 * @return Whether the map is empty.
	 */

	public final boolean isEmpty() {
		return size() == 0;
	}

	/**
	 * Gets all keys.
	 *
	 * @return All keys.
	 */

	public final Set<K> getAllKeys() {
		return map.keySet();
	}

	/**
	 * Gets all values.
	 *
	 * @return All values.
	 */

	public final List<V> getAllValues() {
		final List<V> values = new ArrayList<>();
		for(final Map.Entry<K, List<V>> entry : map.entrySet()) {
			values.addAll(entry.getValue());
		}
		return values;
	}

	/**
	 * Clears the map.
	 */

	public final void clear() {
		map.clear();
	}

}