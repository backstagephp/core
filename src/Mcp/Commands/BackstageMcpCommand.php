<?php

namespace Backstage\Mcp\Commands;

use Backstage\Mcp\BackstageMcpServer;
use Illuminate\Console\Command;

class BackstageMcpCommand extends Command
{
    protected $signature = 'backstage:mcp {action=info} {--tool=} {--args=}';
    protected $description = 'Manage Backstage MCP server and tools';

    public function handle(): int
    {
        $action = $this->argument('action');
        $mcpServer = app(BackstageMcpServer::class);

        return match ($action) {
            'info' => $this->showInfo(),
            'tools' => $this->listTools($mcpServer),
            'test' => $this->testTool($mcpServer),
            default => $this->showHelp()
        };
    }

    private function showInfo(): int
    {
        $this->info('Backstage MCP Server');
        $this->line('');
        $this->line('This MCP server provides tools for Backstage CMS knowledge retrieval.');
        $this->line('');
        $this->line('Available commands:');
        $this->line('  php artisan backstage:mcp tools     - List all available tools');
        $this->line('  php artisan backstage:mcp test       - Test a specific tool');
        $this->line('');

        return self::SUCCESS;
    }

    private function listTools(BackstageMcpServer $mcpServer): int
    {
        $tools = $mcpServer->getTools();

        $this->info('Available Backstage MCP Tools:');
        $this->line('');

        foreach ($tools as $toolId => $tool) {
            $this->line("<comment>{$tool['name']}</comment>");
            $this->line("  {$tool['description']}");
            $this->line('');
        }

        return self::SUCCESS;
    }

    private function testTool(BackstageMcpServer $mcpServer): int
    {
        $tool = $this->option('tool');
        $args = $this->option('args');

        if (!$tool) {
            $this->error('Please specify a tool with --tool option');
            return self::FAILURE;
        }

        $arguments = [];
        if ($args) {
            $arguments = json_decode($args, true) ?? [];
        }

        $this->info("Testing tool: {$tool}");
        $this->line('Arguments: ' . json_encode($arguments, JSON_PRETTY_PRINT));
        $this->line('');

        try {
            $result = $mcpServer->handleToolCall($tool, $arguments);
            $this->line('Result:');
            $this->line(json_encode($result, JSON_PRETTY_PRINT));
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function showHelp(): int
    {
        $this->line('Usage: php artisan backstage:mcp <action> [options]');
        $this->line('');
        $this->line('Actions:');
        $this->line('  info      Show server information (default)');
        $this->line('  tools     List all available tools');
        $this->line('  test      Test a specific tool');
        $this->line('');
        $this->line('Options:');
        $this->line('  --tool    Tool name for testing');
        $this->line('  --args    JSON arguments for testing');

        return self::SUCCESS;
    }
}