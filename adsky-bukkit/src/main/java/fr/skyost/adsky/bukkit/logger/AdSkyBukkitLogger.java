package fr.skyost.adsky.bukkit.logger;

import fr.skyost.adsky.core.AdSkyLogger;
import org.bukkit.Bukkit;
import org.bukkit.ChatColor;
import org.bukkit.plugin.Plugin;

public class AdSkyBukkitLogger implements AdSkyLogger {

	/**
	 * The plugin's instance.
	 */

	private Plugin plugin;

	/**
	 * Creates a new logger instance.
	 *
	 * @param plugin The plugin this logger belongs to.
	 */

	public AdSkyBukkitLogger(final Plugin plugin) {
		this.plugin = plugin;
	}

	@Override
	public final void message(String message) {
		log(message, ChatColor.GOLD);
	}

	@Override
	public final void error(String message) {
		log(message, ChatColor.DARK_RED);
	}

	@Override
	public final void success(String message) {
		log(message, ChatColor.DARK_GREEN);
	}

	@Override
	public final void log(String message) {
		log(message, null);
	}

	/**
	 * Logs a message to the console.
	 *
	 * @param message The message.
	 * @param color The required color.
	 */

	public final void log(final String message, final ChatColor color) {
		Bukkit.getConsoleSender().sendMessage((color == null ? "" : color) + "[" + plugin.getName() + "] " + message);
	}

}