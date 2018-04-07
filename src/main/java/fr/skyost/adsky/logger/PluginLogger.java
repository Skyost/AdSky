package fr.skyost.adsky.logger;

import org.bukkit.Bukkit;
import org.bukkit.ChatColor;
import org.bukkit.plugin.Plugin;

public class PluginLogger {

	private Plugin plugin;

	public PluginLogger(final Plugin plugin) {
		this.plugin = plugin;
	}

	public final Plugin getPlugin() {
		return plugin;
	}

	public final void setPlugin(final Plugin plugin) {
		this.plugin = plugin;
	}

	public final void error(final String message) {
		log(message, ChatColor.RED);
	}

	public final void success(final String message) {
		log(message, ChatColor.GREEN);
	}

	public final void log(final String message, final ChatColor color) {
		Bukkit.getConsoleSender().sendMessage(color + "[" + plugin.getName() + "] " + message);
	}

}