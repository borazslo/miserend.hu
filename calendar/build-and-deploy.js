const { spawn } = require("child_process");

console.log("[NODE] Angular build watcher started...");

const ng = spawn("npx", ["ng", "build", "--watch"], { shell: true });

ng.stdout.on("data", (data) => {
  const output = data.toString();
  process.stdout.write(output);

  if (output.includes("Application bundle generation complete")) {
    console.log("[NODE] Start deploy script...");

    const py = spawn("python", ["../docker/miserend/calendar_deploy.py"], { shell: true });

    py.stdout.on("data", (d) => {
      process.stdout.write(`[PYTHON STDOUT] ${d}`);
    });

    py.stderr.on("data", (d) => {
      process.stderr.write(`[PYTHON STDERR] ${d}`);
    });

    py.on("close", (code) => {
      console.log(`[NODE] Deploy script ended (exit code: ${code})`);
    });
  }

});

ng.stderr.on("data", (data) => {
  process.stderr.write(`[NG STDERR] ${data}`);
});

ng.on("close", (code) => {
  console.log(`[NODE] Angular watcher leállt (exit code: ${code})`);
});
