package fr.skyost.adsky.logger;

import org.bukkit.Bukkit;
import org.bukkit.ChatColor;
import org.bukkit.plugin.Plugin;

/**
 * Represents a logger that allows to use ChatColor codes.
 */

public class PluginLogger {

	/**
	 * The plugin's instance.
	 */

	private Plugin plugin;

	/**
	 * Creates a new logger instance.
	 *
	 * @param plugin The plugin this logger belongs to.
	 */

	public PluginLogger(final Plugin plugin) {
		this.plugin = plugin;
	}

	/**
	 * Allows you to get the plugin this logger belongs to.
	 *
	 * @return The plugin this logger belongs to.
	 */

	public final Plugin getPlugin() {
		return plugin;
	}

	/**
	 * Sets the logger's plugin.
	 *
	 * @param plugin The new plugin.
	 */

	public final void setPlugin(final Plugin plugin) {
		this.plugin = plugin;
	}

	/**
	 * Logs an error.
	 *
	 * @param message The error message.
	 */

	public final void error(final String message) {
		log(message, ChatColor.RED);
	}

	/**
	 * Logs a success.
	 *
	 * @param message The success message.
	 */

	public final void success(final String message) {
		log(message, ChatColor.GREEN);
	}

	/**
	 * Logs a message.
	 *
	 * @param message The message.
	 */

	public final void message(final String message) {
		log(message, ChatColor.GOLD);
	}

	/**
	 * Logs a String.
	 *
	 * @param message The String.
	 */

	public final void log(final String message) {
		log(message, null);
	}

	/**
	 * Logs a String.
	 *
	 * @param message The String.
	 * @param color The color code.
	 */

	public final void log(final String message, final ChatColor color) {
		Bukkit.getConsoleSender().sendMessage((color == null ? "" : color) + "[" + plugin.getName() + "] " + message);
	}

}