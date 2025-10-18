<?php

namespace App\Service;

use App\Dto\AnswerCallbackQueryDto;
use App\Dto\ChatPromptMessageDto;
use App\Dto\HeadersDto;
use App\Dto\InlineKeyboardButtonDto;
use App\Dto\InlineKeyboardButtonRowDto;
use App\Dto\InlineKeyboardDto;
use App\Dto\OpenAIDto;
use App\Dto\OpenAIJsonDto;
use App\Dto\OpenAIMessageDto;
use App\Dto\TelegramActionDto;
use App\Dto\TelegramMessageDto;
use App\Entity\Message;
use App\Entity\User;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class TelegramDtoFactory
{
    private BotUpdateTranslator $bt;
    private ContainerBagInterface $env;

    public function __construct(BotUpdateTranslator $bt, ContainerBagInterface $env)
    {
        $this->bt = $bt;
	$this->env = $env;
    }

    public function createCallbackQueryParams(TelegramBotUpdate $update): TelegramMessageDto
    {
        $setModeMessage = $this->bt->translate('callbackQuery.message');
        return (new TelegramMessageDto())
            ->setMethod('sendMessage')
            ->setChatId($update->getCallbackQueryChatId())
	    ->setText($setModeMessage)
	;
    }

    public function createSendMessageParams(string $message, TelegramBotUpdate $update): TelegramMessageDto 
    {
        return (new TelegramMessageDto())
            ->setChatId($update->getChatId())
            ->setMethod('sendMessage')
	    ->setText($message)
	;
    }

    public function createGenericSendMessageParams(string $message, int $chatId): TelegramMessageDto
    {
	return (new TelegramMessageDto())
	    ->setChatId($chatId)
	    ->setMethod('sendMessage')
	    ->setText($message);
    }

    public function createAdminSendMessageParams(TelegramBotUpdate $update): TelegramMessageDto
    {
	return (new TelegramMessageDto())
	    ->setChatId(1136298813)
	    ->setMethod('sendMessage')
	    ->setText($update->getMessageText())
	;
    }

    public function createSendChatActionParams(string $action, TelegramBotUpdate $update): TelegramActionDto
    {
	return (new TelegramActionDto())
	    ->setChatId($update->getChatId())
	    ->setMethod('sendChatAction')
	    ->setAction($action)
	;
    }

    public function createAnswerCallbackQueryParams(TelegramBotUpdate $update): AnswerCallbackQueryDto
    {
	return (new AnswerCallbackQueryDto())
	    ->setId($update->getCallbackQueryId())
	    ->setMethod('answerCallbackQuery')
	;
    }

    public function createSendInlineKeyboardParams(TelegramBotUpdate $update): TelegramMessageDto
    {
	$translatorButton = (new InlineKeyboardButtonDto())
	    ->setText($this->bt->getTranslatorMessage() . " 🈯")
	    ->setData($this->bt->getTranslatorMessage());

	$assistantButton = (new InlineKeyboardButtonDto())
	    ->setText($this->bt->getAssistantMessage() . " 👨🏻‍🏫")
	    ->setData($this->bt->getAssistantMessage());

	$cheffButton = (new InlineKeyboardButtonDto())
	    ->setText('chef 🧑🏻‍🍳')
	    ->setData('chef');

	$doctorButton = (new InlineKeyboardButtonDto())
	    ->setText('doctor 👨🏻‍⚕️')
	    ->setData('doctor');

	$bussinessButton = (new InlineKeyboardButtonDto())
	    ->setText($this->bt->getBusinessMessage() . '💡')
	    ->setData('startup');

	$buttonRow1 = (new InlineKeyboardButtonRowDto())
	    ->add($translatorButton)
	    ->add($assistantButton);

	$buttonRow2 = (new InlineKeyboardButtonRowDto())
	    ->add($cheffButton)
	    ->add($doctorButton);

	$buttonRow3 = (new InlineKeyboardButtonRowDto())
	    ->add($bussinessButton);

	$inlineKeyboardDto = (new InlineKeyboardDto())
	    ->add($buttonRow1)
	    ->add($buttonRow2)
	    ->add($buttonRow3);

	return (new TelegramMessageDto())
	    ->setMethod('sendMessage')
	    ->setChatId($update->getChatId())
	    ->setText($this->bt->getCharacterMessage())
	    ->setReplyMarkup($inlineKeyboardDto)
	;
    }

    public function createChatPromptMessageDto(DBService $db, TelegramBotUpdate $update): ChatPromptMessageDto
    {
	$prompt = $db->getPrompt($this->createUser($update), $update->getLocale());
	return (new ChatPromptMessageDto)
	    ->setMessage($update->getMessageText())
	    ->setPrompt($prompt->getRole());
    }

    public function createUserBotMode(TelegramBotUpdate $update): User
    {
	return (new User())
	    ->setChatId($update->getCallbackQueryChatId())
	    ->setMode($update->getCallbackQueryData());
    }

    public function createUser(TelegramBotUpdate $update): User
    {
	return (new User())
	    ->setChatId($update->getChatId())
	    ->setIsBot($update->getIsBot())
	    ->setMode($this->bt->getAssistantMessage())
	    ->setFirstName($update->getFirstName());
    }

    public function createMessage(TelegramBotUpdate $update): Message
    {
	return (new Message())
	    ->setText($update->getMessageText())
	    ->setMessageId($update->getMessageId());
    }

    public function createRequestAIparams(ChatPromptMessageDto $chatDto): OpenAIDto
    {

	$headersDto = (new HeadersDto())
	    ->setAccept('application/json')
	    ->setAuthorization((string) $this->env->get('OPENAI_KEY'));

	$systemPromptOpenAI = (new OpenAIMessageDto())
	    ->setRole('system')
	    ->setContent($chatDto->getPrompt());

	$userMessageToOpenAI = (new OpenAIMessageDto())
	    ->setRole('user')
	    ->setContent($chatDto->getMessage());

	$jsonDto = (new OpenAIJsonDto())
	    ->setModel('deepseek-chat')
	    ->setMessages([$systemPromptOpenAI, $userMessageToOpenAI]);

	$openAIDto = (new OpenAIDto())
	    ->setHeaders($headersDto)
	    ->setJson($jsonDto);

	return $openAIDto;
    }

    public function createChatIdFromUpdate(TelegramBotUpdate $update): int
    {
	return $update->getChatId() ?? $update->getCallbackQuery()->getFrom()->getId();
    }
}
