import asyncio
import random
from aiogram import Bot, Dispatcher
from aiogram.types import ReactionTypeEmoji
from aiogram.client.session.aiohttp import AiohttpSession

TOKEN = "7928900640:AAGjxfPanC-r1gqK_r2u2LnCMfvZXwxDcM8"  # O'zingizning bot tokeningizni kiriting

bot = Bot(token=TOKEN)
dp = Dispatcher()

REACTIONS = ["🔥", "❤️", "👍", "😂", "💯", "😍", "😎", "🎉", "🥳", "👏", "💥", "🤩", "🙌", "😆", "😜",
"💖", "😊", "😏", "🤗", "🎶", "🚀", "✨", "🐱", "🐶", "🍕", "🎮", "🎯", "🤑", "⚽️", "🏆"]

async def get_admin_channels():
"""Bot admin bo'lgan kanallarni aniqlash"""
channels = []
async with bot:
updates = await bot.get_updates(limit=100)
for update in updates:
if update.my_chat_member:
chat = update.my_chat_member.chat
if chat.type == "channel":
channels.append(chat.id)
return list(set(channels))

async def react_to_channels():
"""Admin bo'lgan kanallarda so‘nggi postga emoji bosish"""
async with bot:
channels = await get_admin_channels()
if not channels:
print("⚠️ Bot hech qaysi kanalga admin emas!")
return

for channel_id in channels:
messages = await bot.get_chat_history(channel_id, limit=1)
if messages:
last_message = messages[0]
selected_reactions = random.sample(REACTIONS, min(30, len(REACTIONS)))
for emoji in selected_reactions:
await bot.send_reaction(
chat_id=channel_id,
message_id=last_message.message_id,
reaction=[ReactionTypeEmoji(emoji)]
)
await asyncio.sleep(0.5)
await asyncio.sleep(5)

if __name__ == "__main__":
asyncio.run(react_to_channels())
