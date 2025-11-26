@echo off
Timeout T /60
ngrok http 8000 --url https://nimbus.ngrok.dev
